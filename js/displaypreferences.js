(function () {
  const rootDoc = (window.CFG_GLPI && window.CFG_GLPI.root_doc) ? window.CFG_GLPI.root_doc : '';
  const endpoint = rootDoc + '/ajax/v2/displaypreferences.php';

  const preactGlobal = window.preact;
  const hooksGlobal = window.preactHooks;
  const htmGlobal = window.htm;

  if (!preactGlobal || !hooksGlobal || !htmGlobal) {
    return;
  }

  const { h, render } = preactGlobal;
  const { useEffect, useMemo, useRef, useState } = hooksGlobal;
  const html = htmGlobal.bind(h);

  let openCounter = 0;

  const fetchJson = (data) => {
    const formData = new FormData();
    const csrf = document.querySelector('meta[property="glpi:csrf_token"]');
    if (csrf && csrf.content) {
      formData.append('_glpi_csrf_token', csrf.content);
    }
    Object.entries(data).forEach(([key, value]) => {
      if (Array.isArray(value)) {
        value.forEach((entry) => formData.append(key + '[]', entry));
      } else if (value !== undefined && value !== null) {
        formData.append(key, value);
      }
    });

    return fetch(endpoint, {
      method: 'POST',
      credentials: 'same-origin',
      body: formData,
    }).then((res) => res.json());
  };

  const createModal = () => {
    let modal = document.getElementById('display-preferences-modal');
    if (modal) {
      return modal;
    }

    const wrapper = document.createElement('div');
    wrapper.innerHTML = `
            <div class="modal fade" id="display-preferences-modal" tabindex="-1" aria-labelledby="display-preferences-title" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="display-preferences-title"></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div id="display-preferences-modal-content"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">${window.__ ? window.__('Close') : 'Close'}</button>
                            <button type="button" class="btn btn-primary" id="display-preferences-save">${window.__ ? window.__('Save') : 'Save'}</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    document.body.appendChild(wrapper.firstElementChild);
    modal = document.getElementById('display-preferences-modal');
    return modal;
  };

  const groupAvailable = (available) => {
    const groups = new Map();
    available.forEach((item) => {
      const group = item.group || '';
      if (!groups.has(group)) {
        groups.set(group, []);
      }
      groups.get(group).push(item);
    });
    return Array.from(groups.entries());
  };

  const getSelectedList = (selected, locked) => {
    const normalized = Array.from(new Set(selected.map((id) => Number(id)).filter((id) => !Number.isNaN(id))));
    return [...locked, ...normalized.filter((id) => !locked.includes(id))];
  };

  const DisplayPreferencesApp = ({ itemtype, initialView, onSaved }) => {
    const [loading, setLoading] = useState(true);
    const [view, setView] = useState(initialView);
    const [data, setData] = useState(null);
    const [selected, setSelected] = useState([]);
    const [available, setAvailable] = useState([]);
    const [locked, setLocked] = useState([]);
    const [noremove, setNoremove] = useState([]);
    const [labels, setLabels] = useState({});
    const [saving, setSaving] = useState(false);
    const [message, setMessage] = useState(null);
    const [isVisible, setIsVisible] = useState(false);
    const [showDeleteModal, setShowDeleteModal] = useState(false);
    const [selectedColumnToAdd, setSelectedColumnToAdd] = useState('');
    const gridRef = useRef(null);
    const gridApi = useRef(null);
    const selectedRef = useRef([]);
    const lockedRef = useRef([]);

    const load = (targetView) => {
      setLoading(true);
      setMessage(null);
      fetchJson({ action: 'load', itemtype, view: targetView })
        .then((result) => {
          if (!result.success) {
            setMessage({ type: 'danger', text: result.message || 'Error' });
            return;
          }
          const effectiveView = result.view || targetView;
          const selectedList = getSelectedList(result.selected || [], result.locked || []);
          setData(result);
          if (targetView !== effectiveView) {
            setView(effectiveView);
          }
          setSelected(selectedList);
          setAvailable(result.available || []);
          setLocked(result.locked || []);
          setNoremove(result.noremove || []);
          setLabels(result.labels || {});
        })
        .catch(() => {
          setMessage({ type: 'danger', text: 'Unable to load preferences.' });
        })
        .finally(() => {
          setLoading(false);
        });
    };

    useEffect(() => {
      load(view);
    }, [view, itemtype]);

    useEffect(() => {
      selectedRef.current = selected;
    }, [selected]);

    useEffect(() => {
      lockedRef.current = locked;
    }, [locked]);

    useEffect(() => {
      const modal = document.getElementById('display-preferences-modal');
      if (!modal) {
        return;
      }
      const handleShown = () => setIsVisible(true);
      const handleHidden = () => setIsVisible(false);
      modal.addEventListener('shown.bs.modal', handleShown);
      modal.addEventListener('hidden.bs.modal', handleHidden);
      setIsVisible(modal.classList.contains('show'));

      return () => {
        modal.removeEventListener('shown.bs.modal', handleShown);
        modal.removeEventListener('hidden.bs.modal', handleHidden);
      };
    }, []);

    useEffect(() => {
            if (!gridRef.current || !window.GridStack || !data) {
                return;
            }
            const canRender = isVisible && !(view === 'personal' && !data.has_personal);
      if (!canRender) {
        if (gridApi.current) {
          gridApi.current.off('change');
          gridApi.current.destroy(false);
          gridApi.current = null;
        }
        return;
      }
      if (gridApi.current) {
        gridApi.current.off('change');
        gridApi.current.destroy(false);
        gridApi.current = null;
      }

            const onChange = () => {
                if (!gridApi.current || !gridApi.current.engine) {
                    return;
                }
                const nodes = gridApi.current.engine.nodes.slice().sort((a, b) => a.y - b.y);
        const order = nodes.map((node) => Number(node.el.getAttribute('data-column-id')));
        const normalized = order.filter((id) => !Number.isNaN(id));
        const lockedIds = lockedRef.current.filter((id) => normalized.includes(id));
        const rest = normalized.filter((id) => !lockedIds.includes(id));
        const merged = [...lockedIds, ...rest];
                if (merged.length && merged.every((id, index) => selectedRef.current[index] === id)) {
                    return;
                }
                gridApi.current.compact('list');
                setSelected(merged);
            };

            gridApi.current = window.GridStack.init({
                column: 1,
                cellHeight: 44,
                float: false,
                disableOneColumnMode: true,
                margin: 6,
                layout: 'list',
                draggable: {
                    handle: '.grid-stack-item-content',
                    appendTo: 'body',
                    scroll: true,
                },
                disableResize: true,
            }, gridRef.current);

            if (gridApi.current) {
                gridApi.current.on('change', onChange);
                gridApi.current.compact();
            }

      return () => {
        if (gridApi.current) {
          gridApi.current.off('change', onChange);
          gridApi.current.destroy(false);
          gridApi.current = null;
        }
      };
    }, [data, isVisible, view, itemtype]);

    useEffect(() => {
      if (!gridApi.current || !isVisible || !gridRef.current || !data) {
        return;
      }
      if (view === 'personal' && !data.has_personal) {
        return;
      }
            const items = Array.from(gridRef.current.querySelectorAll('.grid-stack-item'));

      gridApi.current.engine.nodes.slice().forEach((node) => {
        const id = node.el.getAttribute('data-column-id');
        if (!items.find((item) => item.getAttribute('data-column-id') === id)) {
          gridApi.current.removeWidget(node.el, false);
        }
      });

            items.forEach((item, index) => {
                if (!item.gridstackNode) {
                    gridApi.current.makeWidget(item);
                }
                gridApi.current.update(item, { x: 0, y: index, w: 1, h: 1 });
            });

      const lockedSet = new Set(locked);
      items.forEach((item) => {
        const id = Number(item.getAttribute('data-column-id'));
        if (lockedSet.has(id)) {
          gridApi.current.movable(item, false);
        } else {
          gridApi.current.movable(item, true);
        }
      });

        }, [selected, locked, noremove, isVisible, view, itemtype, data]);

    const handleRemove = (id) => {
      if (locked.includes(id) || noremove.includes(id)) {
        return;
      }
      setSelected((prev) => prev.filter((entry) => entry !== id));
    };

    const handleAdd = () => {
      const value = Number(selectedColumnToAdd);
      if (!value) {
        return;
      }
      if (!selected.includes(value)) {
        setSelected((prev) => [...prev, value]);
      }
      setSelectedColumnToAdd('');
    };

    const handleSave = () => {
      if (view === 'personal' && data && !data.has_personal) {
        setMessage({ type: 'danger', text: window.__ ? window.__('Create a personal view before saving.') : 'Create a personal view before saving.' });
        return;
      }
      setSaving(true);
      setMessage(null);
      fetchJson({
        action: 'save',
        itemtype,
        view,
        order: selected.filter((id) => !locked.includes(id))
      })
        .then((result) => {
          if (!result.success) {
            setMessage({ type: 'danger', text: result.message || 'Error' });
            return;
          }
          setMessage({ type: 'success', text: window.__ ? window.__('Saved') : 'Saved' });
          if (typeof onSaved === 'function') {
            onSaved();
          }
        })
        .catch(() => {
          setMessage({ type: 'danger', text: 'Unable to save preferences.' });
        })
        .finally(() => {
          setSaving(false);
        });
    };

    const handleActivatePersonal = () => {
      setSaving(true);
      fetchJson({ action: 'activate_personal', itemtype })
        .then((result) => {
          if (!result.success) {
            setMessage({ type: 'danger', text: result.message || 'Error' });
            return;
          }
          setMessage({ type: 'success', text: window.__ ? window.__('Personal view created') : 'Personal view created' });
          load('personal');
        })
        .catch(() => {
          setMessage({ type: 'danger', text: 'Unable to create personal view.' });
        })
        .finally(() => {
          setSaving(false);
        });
    };

    const handleDeletePersonal = () => {
      setShowDeleteModal(true);
    };

    const handleConfirmDelete = () => {
      setShowDeleteModal(false);
      setSaving(true);
      fetchJson({ action: 'delete_personal', itemtype })
        .then((result) => {
          if (!result.success) {
            setMessage({ type: 'danger', text: result.message || 'Error' });
            return;
          }
          setMessage({ type: 'success', text: window.__ ? window.__('Personal view deleted') : 'Personal view deleted' });
          load('global');
        })
        .catch(() => {
          setMessage({ type: 'danger', text: 'Unable to delete personal view.' });
        })
        .finally(() => {
          setSaving(false);
        });
    };

    const addable = useMemo(() => available.filter((item) => !selected.includes(item.id)), [available, selected]);
    const canAdd = addable.length > 0;

    if (!data) {
      return html`<div class="alert alert-danger">${window.__ ? window.__('Unable to load preferences') : 'Unable to load preferences'}</div>`;
    }

    const showEditor = !(view === 'personal' && !data.has_personal);

    return html`
            <div class="display-preferences">
                ${loading ? html`<div class="alert alert-info">${window.__ ? window.__('Loading...') : 'Loading...'}</div>` : ''}
                ${data.can_personal || data.can_global ? html`
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        ${data.can_personal ? html`
                            <button
                                type="button"
                                class=${'btn btn-sm ' + (view === 'personal' ? 'btn-primary' : 'btn-outline-primary')}
                                onClick=${() => setView('personal')}
                            >${window.__ ? window.__('Personal view') : 'Personal view'}</button>
                        ` : ''}
                        ${data.can_global ? html`
                            <button
                                type="button"
                                class=${'btn btn-sm ' + (view === 'global' ? 'btn-primary' : 'btn-outline-primary')}
                                onClick=${() => setView('global')}
                            >${window.__ ? window.__('Global view') : 'Global view'}</button>
                        ` : ''}
                        ${view === 'personal' && data.has_personal ? html`
                            <button
                                type="button"
                                class="btn btn-sm btn-outline-danger"
                                onClick=${handleDeletePersonal}
                                disabled=${saving}
                            >${window.__ ? window.__('Delete personal view') : 'Delete personal view'}</button>
                        ` : ''}
                    </div>
                ` : ''}

                ${view === 'personal' && !data.has_personal ? html`
                    <div class="alert alert-info">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>${window.__ ? window.__('No personal criteria. Create personal parameters?') : 'No personal criteria. Create personal parameters?'}</div>
                            <button
                                type="button"
                                class="btn btn-sm btn-secondary"
                                onClick=${handleActivatePersonal}
                                disabled=${saving}
                            >${window.__ ? window.__('Create') : 'Create'}</button>
                        </div>
                    </div>
                ` : ''}

                ${message ? html`<div class=${'alert alert-' + message.type}>${message.text}</div>` : ''}

                ${showEditor ? html`
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">${window.__ ? window.__('Selected columns') : 'Selected columns'}</div>
                                <div class="card-body">
                                    <div class="grid-stack" ref=${gridRef}>
                                        ${selected.map((id, index) => {
      const label = labels[id] || id;
      const isLocked = locked.includes(id);
      const isNoRemove = noremove.includes(id);
      return html`
                                                <div
                                                    class="grid-stack-item"
                                                    data-column-id=${id}
                                                    data-locked=${isLocked}
                                                    gs-x="0"
                                                    gs-y=${index}
                                                    gs-w="1"
                                                    gs-h="1"
                                                >
                                                    <div class="grid-stack-item-content d-flex align-items-center justify-content-between">
                                                        <div class="d-flex align-items-center">
                                                            <i class="fas fa-grip-lines me-2"></i>
                                                            <span>${label}</span>
                                                        </div>
                                                        ${isLocked ? html`<button type="button" class="btn btn-sm btn-link text-secondary" disabled><i class="fas fa-lock"></i></button>` : (
          isNoRemove ? html`<span class="badge bg-secondary">${window.__ ? window.__('Required') : 'Required'}</span>` : html`
                                                                <button type="button" class="btn btn-sm btn-link text-danger" onClick=${() => handleRemove(id)}>
                                                                    <i class="fas fa-times"></i>
                                                                </button>
                                                            `
        )}
                                                    </div>
                                                </div>
                                            `;
    })}
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <div class="row align-items-center g-2">
                                        <div class="col-auto">
                                            <select
                                                class="form-select"
                                                value=${selectedColumnToAdd}
                                                onChange=${(e) => setSelectedColumnToAdd(e.target.value)}
                                                disabled=${!canAdd}
                                            >
                                                <option value="">${window.__ ? window.__('Choose a column') : 'Choose a column'}</option>
                                                ${groupAvailable(addable).map(([group, items]) => {
      if (!group) {
        return items.map((item) => html`<option value=${item.id}>${item.name}</option>`);
      }
      return html`<optgroup label=${group}>${items.map((item) => html`<option value=${item.id}>${item.name}</option>`)}</optgroup>`;
    })}
                                            </select>
                                        </div>
                                        <div class="col-auto">
                                            <button
                                                type="button"
                                                class="btn btn-primary"
                                                onClick=${handleAdd}
                                                disabled=${!canAdd || !selectedColumnToAdd}
                                            >
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="text-muted mt-2">
                                        ${window.__ ? window.__('Drag to reorder columns. Locked columns cannot be removed.') : 'Drag to reorder columns. Locked columns cannot be removed.'}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-none" id="display-preferences-save-hook">
                        <button type="button" onClick=${handleSave} disabled=${saving}>${window.__ ? window.__('Save') : 'Save'}</button>
                    </div>
                ` : ''}
                ${showDeleteModal ? html`
                    <div class="modal fade show" style="display: block; background-color: rgba(0,0,0,0.5);" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">${window.__ ? window.__('Delete personal view') : 'Delete personal view'}</h5>
                                    <button type="button" class="btn-close" onClick=${() => setShowDeleteModal(false)} aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <p>${window.__ ? window.__('Are you sure you want to delete your personal view and revert to the global view?') : 'Are you sure you want to delete your personal view and revert to the global view?'}</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" onClick=${() => setShowDeleteModal(false)}>${window.__ ? window.__('Cancel') : 'Cancel'}</button>
                                    <button type="button" class="btn btn-danger" onClick=${handleConfirmDelete}>${window.__ ? window.__('Delete') : 'Delete'}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                ` : ''}
            </div>
        `;
  };

  const renderModalContent = (itemtype, initialView, key) => {
    const content = document.getElementById('display-preferences-modal-content');
    if (!content) {
      return;
    }
    render(html`<${DisplayPreferencesApp} key=${key} itemtype=${itemtype} initialView=${initialView} onSaved=${() => window.location.reload()} />`, content);
  };

  const openModal = (itemtype) => {
    if (!itemtype) {
      return;
    }
    const modal = createModal();
    const title = modal.querySelector('#display-preferences-title');
    if (title) {
      title.textContent = window.__ ? window.__('Select default items to show') : 'Select default items to show';
    }
    openCounter += 1;
    renderModalContent(itemtype, 'personal', `${itemtype}-${openCounter}`);
    const instance = window.bootstrap ? window.bootstrap.Modal.getOrCreateInstance(modal) : null;
    if (instance) {
      instance.show();
    } else {
      modal.classList.add('show');
      modal.style.display = 'block';
    }

    const saveButton = document.getElementById('display-preferences-save');
    if (saveButton) {
      saveButton.onclick = () => {
        const hook = document.querySelector('#display-preferences-save-hook button');
        if (hook) {
          hook.click();
        }
      };
    }
  };

  window.DisplayPreferences = {
    open: openModal,
  };
})();
