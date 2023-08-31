hide_icons =  (e) => {
    $('.bubble-icon').css('display', 'none');
}
hide_sub_icons =  (e) => {
    $('.submenu-icon').css('display', 'none');
}

function activateMenuBubble(){
    $("#bubble").on('mousedown', menuDrag);
    $("#compass-menu").on('click', openMenuBubble);
    $("#star-menu").on('click', openMenuBubbleFavorite);
    menuInit();
    hide_icons =  (e) => {
        $('.bubble-icon').css('display', 'none');
    }
    hide_sub_icons =  (e) => {
        $('.submenu-icon').css('display', 'none');
    }
    $("#bubble").on('mouseenter', (e) => {
        hide_sub_icons();
        hide_icons();
    });
}

function deactivateMenuBubble(){
    $("#bubble").off('mousedown', menuDrag);
    $("#bubble").off('click', openMenuBubble);
    $('.bubble-icon').css('display', 'flex');
    $('.submenu-icon').css('display', 'inline-block');
}
function openMenuBubble(){
    is_menu_open = $('.bubble-icon').eq(1).css('display') != 'none'; //not the first one its favorite
    $('.bubble-icon').css('display', is_menu_open ? 'none' : 'flex');
}
function openMenuBubbleFavorite(){
    is_menu_open = $('.bubble-icon').eq(1).css('display') != 'none'; //not the first one its favorite
    $('#menu-favorite').find('.submenu-icon').css('display', is_menu_open ? 'none' : 'flex');
}

function menuDrag(){
    event.stopPropagation();
    const menuDragEventHandler = (event) => {menuMove(event, this)};
    window.addEventListener("mousemove", menuDragEventHandler);
    window.addEventListener("mouseup", (event) => {
        removeEventListener("mousemove", menuDragEventHandler);
        $.ajax({
            type: "POST",
            url: "../ng/db.changeBubblePos.php",
            data: {
                x: $('#bubble').css('left'),
                y: $('#bubble').css('top')
            },
            
        });
    });
}

function resetMenuBubblePos(){
    $('#bubble').css('left', 100 + 'px');
    $('#bubble').css('top', 100 + 'px');
    $.ajax({
        type: "POST",
        url: "../ng/db.changeBubblePos.php",
        data: {
            x: $('#bubble').css('left'),
            y: $('#bubble').css('top')
        },
        
    });
}
let icon_hover = null;
function menuMove(event, element, Y=null, X=null){
    if (Y == null){
        Y = event.clientY;
        X = event.clientX;
    }
    $('#bubble').css('left', X + 'px');
    $('#bubble').css('top', Y + 'px');
    icons = $('.bubble-icon').slice();
    r = 50;
    length = icons.length;
    delta = 2*Math.PI/(length -1); // full circle
    right_to_up = Math.PI/2;
    mini = 15;
    icons.each( function (i) {
        rad = delta * i - right_to_up;
        x = Math.cos(rad) * r - mini;
        y = Math.sin(rad) * r;
        $(this).css('left', (X + x) + 'px');
        $(this).css('top', (Y + y) + 'px');
        if (true){
            sub_icons = $(this).parent().parent().find('.submenu-icon');
            show_sub_icons = (e) => {
                if (icon_hover){
                    icon_hover.parent().parent().find('.submenu-icon').css('display', 'none');
                }
                icon_hover = $(this);
                // $(this).parent().parent().siblings().find('.bubble-icon').css('color', 'rgba(0, 0, 255, 0.3)');
                $(this).parent().parent().find('.submenu-icon').css('display', 'inline-block');

            }
            $(this).on("mouseenter", show_sub_icons);
            sub_length = sub_icons.length;
            sub_r = r*sub_length/5;
            delta_sub = Math.PI/(sub_length-1); //half circle
            right_to_axis = right_to_up; 
            sub_icons.each( function (j) {
                sub_rad = delta_sub * j - right_to_axis;
                sub_x = Math.cos(sub_rad + rad) * sub_r;
                sub_y = Math.sin(sub_rad + rad) * sub_r;
                $(this).css('left', (X + sub_x + x) + 'px');
                $(this).css('top', (Y  + sub_y + y) + 'px');
                $(this).css('background-position', ((100/sub_length)*j) + '%');
            });
            
        }
    });
    icons = $('#menu-favorite').find('.submenu-icon');
    r = 50;
    length = icons.length;
    delta = 2*Math.PI/(length); // full circle
    right_to_up = Math.PI/2;
    mini = -15;
    icons.each( function (i) {
        rad = delta * i - right_to_up;
        x = Math.cos(rad) * r - mini;
        y = Math.sin(rad) * r;
        $(this).css('left', (event.clientX + x) + 'px');
        $(this).css('top', (event.clientY + y) + 'px');
    });

}

function menuInit(){
    hide_sub_icons();
    hide_icons();
    // $('#bubble').css('left', event.clientX + 'px');
    // $('#bubble').css('top', event.clientY + 'px');
    X = parseInt($('#bubble').css('left').slice(0,-2));
    Y = parseInt($('#bubble').css('top').slice(0,-2));
    console.log(X, Y);
    icons = $('.bubble-icon');
    r = 50;
    length = icons.length;
    delta = 2*Math.PI/(length); // full circle
    right_to_up = Math.PI/2;
    icons.each( function (i) {
        rad = delta * i - right_to_up;
        x = Math.cos(rad) * r;
        y = Math.sin(rad) * r;
        $(this).css('left', (X + x) + 'px');
        $(this).css('top', (Y + y) + 'px');
        $(this).css('background-position', ((100/length)*i) + '%');
        if (i == 1 || i == 4 || i == i){
            sub_icons = $(this).parent().parent().find('.submenu-icon');
            show_sub_icons = (e) => {
                if (icon_hover){
                    icon_hover.parent().parent().find('.submenu-icon').css('display', 'none');
                }
                icon_hover = $(this);
                $(this).parent().parent().find('.submenu-icon').css('display', 'inline-block');

            }
            $(this).on("mouseenter", show_sub_icons);
            sub_length = sub_icons.length;
            sub_r = r*sub_length/5;
            delta_sub = Math.PI/(sub_length-1); //half circle
            right_to_axis = right_to_up; 
            sub_icons.each( function (j) {
                sub_rad = delta_sub * j - right_to_axis;
                sub_x = Math.cos(sub_rad + rad) * sub_r;
                sub_y = Math.sin(sub_rad + rad) * sub_r;
                $(this).css('left', (X + sub_x + x) + 'px');
                $(this).css('top', (Y  + sub_y + y) + 'px');
                $(this).css('background-position', ((100/sub_length)*j) + '%');

            });
            
        }
    });

}

function clearMenuBubble(){
    $('.bubble-icon').removeAttr('style');
    $('.submenu-icon').removeAttr('style');
}
function showMenuPositions(state=null){
    if (state) {
        $('.menu-positions').toggleClass('show', state);
        $('.menu-parameters').toggleClass('show', state);
    } else {
        $('.menu-positions').toggleClass('show');
        $('.menu-parameters').toggleClass('show');
    }
}

function changeMenuPosition(class_name){
    if ($('#main-test').hasClass('menu-top') || class_name == 'menu-top'){
        clearMenuOpen();
        deactivateMenuBubble();
        clearMenuBubble();

    }

    if ($('#main-test').hasClass('menu-bubble')) {
        clearMenuOpen();
        clearMenuBubble();
    }
    
    if (class_name == 'menu-bubble'){
        activateMenuBubble();
        menuInit();
    }
    $('.menu-positions').toggleClass('show', false);
    $('#main-test').attr("class", class_name);
    $.ajax({
        type: "POST",
        url: "../ng/db.changeMenuPosition.php",
        data: {
            position: class_name,
        },
    });
    showMenuPositions(false);
}

function menuFavoriteEnable(enable=true){
    $('#menu-favorite').toggleClass('hidden', !enable);
    $.ajax({
        type: "POST",
        url: "../ng/db.activateMenuFavorite.php",
        data: {
            menu_favorite_on: enable,
        },
    });
}

function clearMenuOpen(){
    menus = $('#menu-content').children('li');
    menus.children('ul').removeClass('show');
    $.ajax({
        type: "POST",
        url: "../ng/db.openMenu.php",
        data: {
        clear: true,
        },
    });
}

function resizeMenu(){
    is_menu_close = $('#menu').hasClass('menu-close');
    width_var = is_menu_close ? "--menu-close-width" : "--menu-open-width";
    $('#menu').css('transition', 'none');
    $('.main-container').css('transition', 'none');
    window.addEventListener("mousemove", changeMenuWidth);
    window.addEventListener("mouseup", (event) => {
        $('#menu').css('transition', 'width var(--menu-transition-time) ease');
        removeEventListener("mousemove", changeMenuWidth);
        $.ajax({
            type: "POST",
            url: "../ng/db.changeMenuSize.php",
            data: {
                menu : is_menu_close ? "menu-close" : "menu-open",
                width: $('body').css(width_var),
            },
        });
    }, { once: true });
}
function changeMenuWidth(event) {
    menu_is_right = $('#main-test').hasClass('menu-right');
    if (menu_is_right){
        $('body').css(width_var,  window.innerWidth - event.clientX + "px")
    } else {
    $('body').css(width_var, event.clientX + "px")
    }
}

function changeMenuState(is_menu_close=null){
    if (is_menu_close == null){
        is_menu_close = $('#menu').hasClass('menu-close');}
    $('#menu').toggleClass('menu-close', !is_menu_close);
    $('#header_top').toggleClass('menu-close', !is_menu_close);
    if (!is_menu_close){
        $('#menu').toggleClass('favorite-mode', false);
    }
    $.ajax({
        type: "POST",
        url: "../ng/db.menuSmall.php",
        data: {
        small: !is_menu_close,
        },
    });
}
function activateFavoriteMode(event, activate=null){
    event.stopPropagation();
    if (activate == null){
        activate = !$('#menu').hasClass('favorite-mode');}
    $('#menu').toggleClass('favorite-mode', activate);

}
function openMenu(item, menu_name){
    if ($('#main-test').hasClass('menu-top')){
        $('.menu:not(#' + this.id + ')').children('ul').collapse('hide');
        return;
    }
    is_menu_open = $(item).hasClass('collapsed');
    $.ajax({
        type: "POST",
        url: "../ng/db.openMenu.php",
        data: {
            clear: false,
            menu_name: menu_name,
            open: is_menu_open,
        },
    });
}
function addFavorite(event, item, menu_name, sub_menu_name){
    event.stopPropagation();
    event.preventDefault();
    ul = document.getElementById('submenu-favorite');
    li = item.parentElement;
    is_menu_favorite = $(li).hasClass('section-favorite');
    $(li).toggleClass('section-favorite', !is_menu_favorite);
    if(is_menu_favorite){ //removing favorite
        if (li.parentElement.id == "submenu-favorite"){ //check if user clicked the star in the favorite menu or in other menu
            $(document).find('#' + li.id.replace('favorite-','')).toggleClass('section-favorite', false);
            li.remove();
        }
        else {
            $(ul).children('#favorite-' + li.id).remove();
            li.remove();
        }
    } else { //adding favorite
        new_li = li.cloneNode(true);
        new_li.id = 'favorite-' + li.id;
        ul.appendChild(new_li);
    }
    $.ajax({
        type: "POST",
        url: "../ng/db.addFavorite.php",
        data: {
        remove: is_menu_favorite,
        menu_name: menu_name,
        submenu_name: sub_menu_name,
        },
    });
}
