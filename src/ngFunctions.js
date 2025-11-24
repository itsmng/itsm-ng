$(document).ready(() => {
  if ($("#main-test").hasClass("menu-top")) {
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
  $("#compact-mode-toggle").on("change", function (e) {
    e.stopPropagation();
    toggleCompactMode(this.checked);
  });
  $("#menu-favorite-selection-toggle").on("change", function (e) {
    e.stopPropagation();
    activateFavoriteMode(e, this.checked);
  });
  $("#menu-options-menu").on("click", showMenuOptions);
  $(".star-icon").on("click", addFavorite);
}

function showMenuOptions() {
  //menu-dropdown for menu position, favorite etc...
  $("#menu-options").toggleClass("show");
}

let hide_sub_icons = () => {
  $(".submenu-icon").css("display", "none");
};

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
  //select menu left, right, top
  if ($("#main-test").hasClass("menu-top") || class_name == "menu-top") {
    clearMenuOpen();
    $("html").css("--nav-top-height", "2rem");
  } else {
    $("html").css("--nav-top-height", "0rem");
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
  //close all menu, for menu top and menu
  const menus = $("#menu-content").children("li");
  menus.children("ul").removeClass("show");
  $.ajax({
    type: "POST",
    url: $("#main-test").data("root") + "/src/menuCommands/openMenu.ajax.php",
    data: {
      clear: true,
    },
  });
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

function toggleCompactMode(enable = false) {
  $.ajax({
    type: "POST",
    url: $("#main-test").data("root") + "/src/menuCommands/compactMode.ajax.php",
    data: {
      compact_mode: enable,
    },
  }).always(function () {
    window.location.reload();
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
  const was_menu_open = $(item).hasClass("collapsed");
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
function addFavorite(e) {
  e.stopPropagation();
  e.preventDefault();
  const menu_name = $(this).parent().data("menu-name");
  const sub_menu_name = $(this).parent().data("submenu-name");
  const ul = document.getElementById("submenu-favorite");
  const li = this.parentElement;
  const is_menu_favorite = $(li).hasClass("section-favorite");
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
    const new_li = li.cloneNode(true);
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

