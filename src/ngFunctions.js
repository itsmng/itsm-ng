$(document).ready((e) => {
  if ($("#main-test").hasClass("menu-bubble")) {
    activateMenuBubble();
  } else if ($("#main-test").hasClass("menu-top")) {
    $("html").css("--nav-top-height", "2rem");
  }
  initializeListeners();
});

function initializeListeners() {
  $(".menu-position-bouton").on("change", function (e) {
    e.stopPropagation();
    changeMenuPosition(this.value);
  });
  $("#menu-collapse-toggle").on("change", function (e) {
    e.stopPropagation();
    changeMenuState(!this.checked);
  });
  $("#menu-favorite-toggle").on("change", function (e) {
    e.stopPropagation();
    menuFavoriteEnable(this.checked);
  });
  $("#menu-favorite-selection-toggle").on("change", function (e) {
    e.stopPropagation();
    activateFavoriteMode(e, this.checked);
  });
  $("#menu-options-menu").on("click", showMenuOptions);
  $("#menu-options-reset").on("click", resetMenuBubblePos);
  $(".star-icon").on("click", addFavorite);
}

function showMenuOptions() {
  //menu-dropdown for menu position, favorite etc...
  $("#menu-options").toggleClass("show");
}

let hide_sub_icons = (e) => {
  $(".submenu-icon").css("display", "none");
};

function activateMenuBubble() {
  $("#bubble").on("mousedown", menuDrag);
  $("#compass-menu").on("click", openMenuCompass);
  $("#star-menu").on("click", openMenuBubbleFavorite);
  menuBubbleInit();
  $("#bubble").on("mouseenter", (e) => {
    hide_sub_icons();
    hide_icons();
  });
}

function deactivateMenuBubble() {
  $("#bubble").off("mousedown", menuDrag);
  $("#compass-menu").off("click", openMenuCompass);
  $("#star-menu").off("click", openMenuBubbleFavorite);
  $(".bubble-icon").css("display", "flex");
  $(".submenu-icon").css("display", "inline-block");
}

function openMenuCompass() {
  //menu bubble left part
  is_menu_open = $(".bubble-icon").eq(1).css("display") != "none"; //not the first one its favorite
  $(".bubble-icon").css("display", is_menu_open ? "none" : "flex");
}
function openMenuBubbleFavorite() {
  //menu bubble rigth part
  is_menu_open = $(".bubble-icon").eq(1).css("display") != "none"; //not the first one its favorite
  $("#menu-favorite")
    .find(".submenu-icon")
    .css("display", is_menu_open ? "none" : "flex");
}

function menuDrag() {
  //to move menu bubble
  //TODO: add limit to menu bubble pos to prevent going out of screen, meantime reset to 'default'
  event.stopPropagation();
  const menuDragEventHandler = (event) => {
    menuMove(event, this);
  };
  window.addEventListener("mousemove", menuDragEventHandler);
  window.addEventListener("mouseup", (event) => {
    removeEventListener("mousemove", menuDragEventHandler);
    $.ajax({
      type: "POST",
      url: $("#main-test").data("root") + "/src/menuCommands/menuCommands/changeBubblePos.ajax.php",
      data: {
        x: $("#bubble").css("left"),
        y: $("#bubble").css("top"),
      },
    });
  });
}

function resetMenuBubblePos() {
  //TODO: add limit to menu bubble pos to prevent going out of screen, meantime reset to 'default'
  $("#bubble").css("left", 100 + "px");
  $("#bubble").css("top", 100 + "px");
  $.ajax({
    type: "POST",
    url: $("#main-test").data("root") + "/src/menuCommands/changeBubblePos.ajax.php",
    data: {
      x: $("#bubble").css("left"),
      y: $("#bubble").css("top"),
    },
  });
}

let icon_hover = null;
//caclulate menu position based on mouse position each move event, use radians and trigonometry to calculate position
function menuMove(event, element, Y = null, X = null) {
  //highly inneficent,
  //initialize menu and submenu pos relative to bubble menu on first page load or menu update
  //then store and retrieve when needed
  if (Y == null) {
    Y = event.clientY;
    X = event.clientX;
  }
  $("#bubble").css("left", X + "px");
  $("#bubble").css("top", Y + "px");
  icons = $(".bubble-icon");
  //divise 2PI (cercle entier) par nombre d'icone pour avoir l'angle entre chaque icone
  //multiplié par le rayon et décaler de 0.5PI pour avoir le premier icone en haut
  r = 50;
  length = icons.length;
  delta = (2 * Math.PI) / (length - 1); // full circle
  right_to_up = Math.PI / 2;
  mini = 15;
  icons.each(function (i) {
    rad = delta * i - right_to_up;
    x = Math.cos(rad) * r - mini;
    y = Math.sin(rad) * r;
    $(this).css("left", X + x + "px");
    $(this).css("top", Y + y + "px");
    $(this).css("background-position", (100 / length) * i + "%");
    sub_icons = $(this).parent().parent().find(".submenu-icon");
    show_sub_icons = (e) => {
      if (icon_hover) {
        icon_hover.parent().parent().find(".submenu-icon").css("display", "none");
      }
      icon_hover = $(this);
      $(this).parent().parent().find(".submenu-icon").css("display", "inline-block");
    };
    $(this).on("mouseenter", show_sub_icons);
    sub_length = sub_icons.length;
    sub_r = (r * sub_length) / 5;
    delta_sub = Math.PI / (sub_length - 1); //half circle
    right_to_axis = right_to_up;
    sub_icons.each(function (j) {
      sub_rad = delta_sub * j - right_to_axis;
      sub_x = Math.cos(sub_rad + rad) * sub_r;
      sub_y = Math.sin(sub_rad + rad) * sub_r;
      $(this).css("left", X + sub_x + x + "px");
      $(this).css("top", Y + sub_y + y + "px");
      $(this).css("background-position", (100 / sub_length) * j + "%");
    });
  });
  icons = $("#menu-favorite").find(".submenu-icon");
  r = 50;
  length = icons.length;
  delta = (2 * Math.PI) / length; // full circle
  right_to_up = Math.PI / 2;
  mini = -15;
  icons.each(function (i) {
    rad = delta * i - right_to_up;
    x = Math.cos(rad) * r - mini;
    y = Math.sin(rad) * r;
    $(this).css("left", X + x + "px");
    $(this).css("top", Y + y + "px");
  });
}

function menuBubbleInit() {
  hide_sub_icons();
  hide_icons();
  X = parseInt($("#bubble").css("left").slice(0, -2));
  Y = parseInt($("#bubble").css("top").slice(0, -2));
  menuMove(null, this, Y, X);
}

function clearMenuBubble() {
  $(".bubble-icon").removeAttr("style");
  $(".submenu-icon").removeAttr("style");
}
function showMenuPositions(state = null) {
  if (state) {
    $(".menu-positions").toggleClass("show", state);
    $(".menu-parameters").toggleClass("show", state);
  } else {
    $(".menu-positions").toggleClass("show");
    $(".menu-parameters").toggleClass("show");
  }
}

function changeMenuPosition(class_name) {
  //select menu left, right, top, bubble
  if ($("#main-test").hasClass("menu-top") || class_name == "menu-top") {
    clearMenuOpen();
    deactivateMenuBubble();
    $("html").css("--nav-top-height", "2rem");
  } else {
    $("html").css("--nav-top-height", "0rem");
  }

  if ($("#main-test").hasClass("menu-bubble")) {
    clearMenuOpen();
    clearMenuBubble();
  }

  if (class_name == "menu-bubble") {
    activateMenuBubble();
  }
  $(".menu-positions").toggleClass("show", false);
  $("#main-test").attr("class", class_name);
  $.ajax({
    type: "POST",
    url: $("#main-test").data("root") + "/src/menuCommands/changeMenuPosition.ajax.php",
    data: {
      position: class_name,
    },
  });
  showMenuPositions(false);
}

function menuFavoriteEnable(enable = true) {
  $("#menu-favorite").toggleClass("hidden", !enable);
  $.ajax({
    type: "POST",
    url: $("#main-test").data("root") + "/src/menuCommands/activateMenuFavorite.ajax.php",
    data: {
      menu_favorite_on: enable,
    },
  });
}

function clearMenuOpen() {
  //close all menu, for menu top and menu bubble
  menus = $("#menu-content").children("li");
  menus.children("ul").removeClass("show");
  $.ajax({
    type: "POST",
    url: $("#main-test").data("root") + "/src/menuCommands/openMenu.ajax.php",
    data: {
      clear: true,
    },
  });
}

function changeMenuWidth(event) {
  //TODO: remove resizing on menu collapsed
  menu_is_right = $("#main-test").hasClass("menu-right");
  min = 0;
  max = window.innerWidth;
  if (event.clientX < min || event.clientX > max) {
    return;
  }
  if (menu_is_right) {
    $("body").css(width_var, window.innerWidth - event.clientX + "px");
  } else {
    $("body").css(width_var, event.clientX + "px");
  }
}

function changeMenuState(is_menu_close = null) {
  //whether menu is open or collapsed
  if (is_menu_close == null) {
    is_menu_close = $("#menu").hasClass("menu-close");
  }
  $("#menu").toggleClass("menu-close", !is_menu_close);
  $("#header_top").toggleClass("menu-close", !is_menu_close);
  if (!is_menu_close) {
    $("#menu").toggleClass("favorite-mode", false);
  }
  $.ajax({
    type: "POST",
    url: $("#main-test").data("root") + "/src/menuCommands/menuSmall.ajax.php",
    data: {
      small: !is_menu_close,
    },
  });
}

function activateFavoriteMode(event, activate = null) {
  event.stopPropagation();
  if (activate == null) {
    activate = !$("#menu").hasClass("favorite-mode");
  }
  $("#menu").toggleClass("favorite-mode", activate);
}
function openMenu(item, menu_name) {
  //uncollapsed menu
  if ($("#main-test").hasClass("menu-top")) {
    $(".menu:not(#" + this.id + ")")
      .children("ul")
      .collapse("hide");
    return;
  }
  was_menu_open = $(item).hasClass("collapsed");
  $.ajax({
    type: "POST",
    url: $("#main-test").data("root") + "/src/menuCommands/openMenu.ajax.php",
    data: {
      clear: false,
      menu_name: menu_name,
      open: !was_menu_open,
    },
  });
}
function addFavorite() {
  event.stopPropagation();
  event.preventDefault();
  menu_name = $(this).parent().data("menu-name");
  sub_menu_name = $(this).parent().data("submenu-name");
  ul = document.getElementById("submenu-favorite");
  li = this.parentElement;
  is_menu_favorite = $(li).hasClass("section-favorite");
  $(li).toggleClass("section-favorite", !is_menu_favorite);
  if (is_menu_favorite) {
    //removing favorite
    if (li.parentElement.id == "submenu-favorite") {
      //check if user clicked the star in the favorite menu or in other menu
      $(document)
        .find("#" + li.id.replace("favorite-", ""))
        .toggleClass("section-favorite", false);
      li.remove();
    } else {
      $(ul)
        .children("#favorite-" + li.id)
        .toggleClass("section-favorite", false);
      $("#favorite-" + li.id).remove();
    }
  } else {
    //adding favorite
    new_li = li.cloneNode(true);
    new_li.id = "favorite-" + li.id;
    ul.appendChild(new_li);
  }
  $.ajax({
    type: "POST",
    url: $("#main-test").data("root") + "/src/menuCommands/addFavorite.ajax.php",
    data: {
      remove: is_menu_favorite,
      menu_name: menu_name,
      submenu_name: sub_menu_name,
    },
  });
}
