(() => {
  var __defProp = Object.defineProperty;
  var __getOwnPropNames = Object.getOwnPropertyNames;
  var __getOwnPropDesc = Object.getOwnPropertyDescriptor;
  var __hasOwnProp = Object.prototype.hasOwnProperty;
  var __moduleCache = /* @__PURE__ */ new WeakMap;
  var __toCommonJS = (from) => {
    var entry = __moduleCache.get(from), desc;
    if (entry)
      return entry;
    entry = __defProp({}, "__esModule", { value: true });
    if (from && typeof from === "object" || typeof from === "function")
      __getOwnPropNames(from).map((key) => !__hasOwnProp.call(entry, key) && __defProp(entry, key, {
        get: () => from[key],
        enumerable: !(desc = __getOwnPropDesc(from, key)) || desc.enumerable
      }));
    __moduleCache.set(from, entry);
    return entry;
  };
  var __export = (target, all) => {
    for (var name in all)
      __defProp(target, name, {
        get: all[name],
        enumerable: true,
        configurable: true,
        set: (newValue) => all[name] = () => newValue
      });
  };

  // node_modules/nanostores/index.js
  var exports_nanostores = {};
  __export(exports_nanostores, {
    task: () => task,
    subscribeKeys: () => subscribeKeys,
    startTask: () => startTask,
    setPath: () => setPath,
    setByKey: () => setByKey,
    readonlyType: () => readonlyType,
    onStop: () => onStop,
    onStart: () => onStart,
    onSet: () => onSet,
    onNotify: () => onNotify,
    onMount: () => onMount,
    mapCreator: () => mapCreator,
    map: () => map,
    listenKeys: () => listenKeys,
    keepMount: () => keepMount,
    getPath: () => getPath,
    getKey: () => getKey,
    effect: () => effect,
    deepMap: () => deepMap,
    computed: () => computed,
    cleanTasks: () => cleanTasks,
    cleanStores: () => cleanStores,
    clean: () => clean,
    batched: () => batched,
    atom: () => atom,
    allTasks: () => allTasks,
    STORE_UNMOUNT_DELAY: () => STORE_UNMOUNT_DELAY
  });

  window.Nanostores = exports_nanostores;

  // node_modules/nanostores/task/index.js
  var tasks = 0;
  var resolves = [];
  function startTask() {
    tasks += 1;
    return () => {
      tasks -= 1;
      if (tasks === 0) {
        let prevResolves = resolves;
        resolves = [];
        for (let i of prevResolves)
          i();
      }
    };
  }
  function task(cb) {
    let endTask = startTask();
    let promise = cb().finally(endTask);
    promise.t = true;
    return promise;
  }
  function allTasks() {
    if (tasks === 0) {
      return Promise.resolve();
    } else {
      return new Promise((resolve) => {
        resolves.push(resolve);
      });
    }
  }
  function cleanTasks() {
    tasks = 0;
  }

  // node_modules/nanostores/clean-stores/index.js
  var clean = Symbol("clean");
  var cleanStores = (...stores) => {
    if (false) {}
    cleanTasks();
    for (let $store of stores) {
      if ($store) {
        if ($store.mocked)
          delete $store.mocked;
        if ($store[clean])
          $store[clean]();
      }
    }
  };

  // node_modules/nanostores/atom/index.js
  var listenerQueue = [];
  var lqIndex = 0;
  var QUEUE_ITEMS_PER_LISTENER = 4;
  var epoch = 0;
  var atom = (initialValue) => {
    let listeners = [];
    let $atom = {
      get() {
        if (!$atom.lc) {
          $atom.listen(() => {})();
        }
        return $atom.value;
      },
      lc: 0,
      listen(listener) {
        $atom.lc = listeners.push(listener);
        return () => {
          for (let i = lqIndex + QUEUE_ITEMS_PER_LISTENER;i < listenerQueue.length; ) {
            if (listenerQueue[i] === listener) {
              listenerQueue.splice(i, QUEUE_ITEMS_PER_LISTENER);
            } else {
              i += QUEUE_ITEMS_PER_LISTENER;
            }
          }
          let index = listeners.indexOf(listener);
          if (~index) {
            listeners.splice(index, 1);
            if (!--$atom.lc)
              $atom.off();
          }
        };
      },
      notify(oldValue, changedKey) {
        epoch++;
        let runListenerQueue = !listenerQueue.length;
        for (let listener of listeners) {
          listenerQueue.push(listener, $atom.value, oldValue, changedKey);
        }
        if (runListenerQueue) {
          for (lqIndex = 0;lqIndex < listenerQueue.length; lqIndex += QUEUE_ITEMS_PER_LISTENER) {
            listenerQueue[lqIndex](listenerQueue[lqIndex + 1], listenerQueue[lqIndex + 2], listenerQueue[lqIndex + 3]);
          }
          listenerQueue.length = 0;
        }
      },
      off() {},
      set(newValue) {
        let oldValue = $atom.value;
        if (oldValue !== newValue) {
          $atom.value = newValue;
          $atom.notify(oldValue);
        }
      },
      subscribe(listener) {
        let unbind = $atom.listen(listener);
        listener($atom.value);
        return unbind;
      },
      value: initialValue
    };
    if (true) {
      $atom[clean] = () => {
        listeners = [];
        $atom.lc = 0;
        $atom.off();
      };
    }
    return $atom;
  };
  var readonlyType = (store) => store;
  // node_modules/nanostores/lifecycle/index.js
  var START = 0;
  var STOP = 1;
  var SET = 2;
  var NOTIFY = 3;
  var MOUNT = 5;
  var UNMOUNT = 6;
  var REVERT_MUTATION = 10;
  var on = (object, listener, eventKey, mutateStore) => {
    object.events = object.events || {};
    if (!object.events[eventKey + REVERT_MUTATION]) {
      object.events[eventKey + REVERT_MUTATION] = mutateStore((eventProps) => {
        object.events[eventKey].reduceRight((event, l) => (l(event), event), {
          shared: {},
          ...eventProps
        });
      });
    }
    object.events[eventKey] = object.events[eventKey] || [];
    object.events[eventKey].push(listener);
    return () => {
      let currentListeners = object.events[eventKey];
      let index = currentListeners.indexOf(listener);
      currentListeners.splice(index, 1);
      if (!currentListeners.length) {
        delete object.events[eventKey];
        object.events[eventKey + REVERT_MUTATION]();
        delete object.events[eventKey + REVERT_MUTATION];
      }
    };
  };
  var onStart = ($store, listener) => on($store, listener, START, (runListeners) => {
    let originListen = $store.listen;
    $store.listen = (arg) => {
      if (!$store.lc && !$store.starting) {
        $store.starting = true;
        runListeners();
        delete $store.starting;
      }
      return originListen(arg);
    };
    return () => {
      $store.listen = originListen;
    };
  });
  var onStop = ($store, listener) => on($store, listener, STOP, (runListeners) => {
    let originOff = $store.off;
    $store.off = () => {
      runListeners();
      originOff();
    };
    return () => {
      $store.off = originOff;
    };
  });
  var onSet = ($store, listener) => on($store, listener, SET, (runListeners) => {
    let originSet = $store.set;
    let originSetKey = $store.setKey;
    if ($store.setKey) {
      $store.setKey = (changed, changedValue) => {
        let isAborted;
        let abort = () => {
          isAborted = true;
        };
        runListeners({
          abort,
          changed,
          newValue: { ...$store.value, [changed]: changedValue }
        });
        if (!isAborted)
          return originSetKey(changed, changedValue);
      };
    }
    $store.set = (newValue) => {
      let isAborted;
      let abort = () => {
        isAborted = true;
      };
      runListeners({ abort, newValue });
      if (!isAborted)
        return originSet(newValue);
    };
    return () => {
      $store.set = originSet;
      $store.setKey = originSetKey;
    };
  });
  var onNotify = ($store, listener) => on($store, listener, NOTIFY, (runListeners) => {
    let originNotify = $store.notify;
    $store.notify = (oldValue, changed) => {
      let isAborted;
      let abort = () => {
        isAborted = true;
      };
      runListeners({ abort, changed, oldValue });
      if (!isAborted)
        return originNotify(oldValue, changed);
    };
    return () => {
      $store.notify = originNotify;
    };
  });
  var STORE_UNMOUNT_DELAY = 1000;
  var onMount = ($store, initialize) => {
    let listener = (payload) => {
      let destroy = initialize(payload);
      if (destroy)
        $store.events[UNMOUNT].push(destroy);
    };
    return on($store, listener, MOUNT, (runListeners) => {
      let originListen = $store.listen;
      $store.listen = (...args) => {
        if (!$store.lc && !$store.active) {
          $store.active = true;
          runListeners();
        }
        return originListen(...args);
      };
      let originOff = $store.off;
      $store.events[UNMOUNT] = [];
      $store.off = () => {
        originOff();
        setTimeout(() => {
          if ($store.active && !$store.lc) {
            $store.active = false;
            for (let destroy of $store.events[UNMOUNT])
              destroy();
            $store.events[UNMOUNT] = [];
          }
        }, STORE_UNMOUNT_DELAY);
      };
      if (true) {
        let originClean = $store[clean];
        $store[clean] = () => {
          for (let destroy of $store.events[UNMOUNT])
            destroy();
          $store.events[UNMOUNT] = [];
          $store.active = false;
          originClean();
        };
      }
      return () => {
        $store.listen = originListen;
        $store.off = originOff;
      };
    });
  };

  // node_modules/nanostores/computed/index.js
  var computedStore = (stores, cb, batched) => {
    if (!Array.isArray(stores))
      stores = [stores];
    let previousArgs;
    let currentEpoch;
    let set = () => {
      if (currentEpoch === epoch)
        return;
      currentEpoch = epoch;
      let args = stores.map(($store) => $store.get());
      if (!previousArgs || args.some((arg, i) => arg !== previousArgs[i])) {
        previousArgs = args;
        let value = cb(...args);
        if (value && value.then && value.t) {
          value.then((asyncValue) => {
            if (previousArgs === args) {
              $computed.set(asyncValue);
            }
          });
        } else {
          $computed.set(value);
          currentEpoch = epoch;
        }
      }
    };
    let $computed = atom(undefined);
    let get = $computed.get;
    $computed.get = () => {
      set();
      return get();
    };
    let timer;
    let run = batched ? () => {
      clearTimeout(timer);
      timer = setTimeout(set);
    } : set;
    onMount($computed, () => {
      let unbinds = stores.map(($store) => $store.listen(run));
      set();
      return () => {
        for (let unbind of unbinds)
          unbind();
      };
    });
    return $computed;
  };
  var computed = (stores, fn) => computedStore(stores, fn);
  var batched = (stores, fn) => computedStore(stores, fn, true);
  // node_modules/nanostores/deep-map/path.js
  function getPath(obj, path) {
    let allKeys = getAllKeysFromPath(path);
    let res = obj;
    for (let key of allKeys) {
      if (res === undefined) {
        break;
      }
      res = res[key];
    }
    return res;
  }
  function setPath(obj, path, value) {
    return setByKey(obj != null ? obj : {}, getAllKeysFromPath(path), value);
  }
  function setByKey(obj, splittedKeys, value) {
    let key = splittedKeys[0];
    let copy = Array.isArray(obj) ? [...obj] : { ...obj };
    if (splittedKeys.length === 1) {
      if (value === undefined) {
        if (Array.isArray(copy)) {
          copy.splice(key, 1);
        } else {
          delete copy[key];
        }
      } else {
        copy[key] = value;
      }
      return copy;
    }
    ensureKey(copy, key, splittedKeys[1]);
    copy[key] = setByKey(copy[key], splittedKeys.slice(1), value);
    return copy;
  }
  var ARRAY_INDEX = /(.*)\[(\d+)\]/;
  function getAllKeysFromPath(path) {
    return path.split(".").flatMap((key) => getKeyAndIndicesFromKey(key));
  }
  function getKeyAndIndicesFromKey(key) {
    if (ARRAY_INDEX.test(key)) {
      let [, keyPart, index] = key.match(ARRAY_INDEX);
      return [...getKeyAndIndicesFromKey(keyPart), index];
    }
    return [key];
  }
  var IS_NUMBER = /^\d+$/;
  function ensureKey(obj, key, nextKey) {
    if (key in obj) {
      return;
    }
    let isNum = IS_NUMBER.test(nextKey);
    if (isNum) {
      obj[key] = Array(parseInt(nextKey, 10) + 1);
    } else {
      obj[key] = {};
    }
  }

  // node_modules/nanostores/deep-map/index.js
  function deepMap(initial = {}) {
    let $deepMap = atom(initial);
    $deepMap.setKey = (key, value) => {
      if (getPath($deepMap.value, key) !== value) {
        let oldValue = $deepMap.value;
        $deepMap.value = setPath($deepMap.value, key, value);
        $deepMap.notify(oldValue, key);
      }
    };
    return $deepMap;
  }
  function getKey(store, key) {
    let value = store.get();
    return getPath(value, key);
  }
  // node_modules/nanostores/effect/index.js
  var effect = (stores, callback) => {
    if (!Array.isArray(stores))
      stores = [stores];
    let unbinds = [];
    let lastRunUnbind;
    let run = () => {
      lastRunUnbind && lastRunUnbind();
      let values = stores.map((store) => store.get());
      lastRunUnbind = callback(...values);
    };
    unbinds = stores.map((store) => store.listen(run));
    run();
    return () => {
      unbinds.forEach((unbind) => unbind());
      lastRunUnbind && lastRunUnbind();
    };
  };
  // node_modules/nanostores/keep-mount/index.js
  var keepMount = ($store) => {
    $store.listen(() => {});
  };
  // node_modules/nanostores/listen-keys/index.js
  function listenKeys($store, keys, listener) {
    let keysSet = new Set(keys).add(undefined);
    return $store.listen((value, oldValue, changed) => {
      if (keysSet.has(changed)) {
        listener(value, oldValue, changed);
      }
    });
  }
  function subscribeKeys($store, keys, listener) {
    let unbind = listenKeys($store, keys, listener);
    listener($store.value);
    return unbind;
  }
  // node_modules/nanostores/map/index.js
  var map = (initial = {}) => {
    let $map = atom(initial);
    $map.setKey = function(key, value) {
      let oldMap = $map.value;
      if (typeof value === "undefined" && key in $map.value) {
        $map.value = { ...$map.value };
        delete $map.value[key];
        $map.notify(oldMap, key);
      } else if ($map.value[key] !== value) {
        $map.value = {
          ...$map.value,
          [key]: value
        };
        $map.notify(oldMap, key);
      }
    };
    return $map;
  };

  // node_modules/nanostores/map-creator/index.js
  function mapCreator(init) {
    let Creator = (id, ...args) => {
      if (!Creator.cache[id]) {
        Creator.cache[id] = Creator.build(id, ...args);
      }
      return Creator.cache[id];
    };
    Creator.build = (id, ...args) => {
      let store = map({ id });
      onMount(store, () => {
        let destroy;
        if (init)
          destroy = init(store, id, ...args);
        return () => {
          delete Creator.cache[id];
          if (destroy)
            destroy();
        };
      });
      return store;
    };
    Creator.cache = {};
    if (true) {
      Creator[clean] = () => {
        for (let id in Creator.cache) {
          Creator.cache[id][clean]();
        }
        Creator.cache = {};
      };
    }
    return Creator;
  }
})();
