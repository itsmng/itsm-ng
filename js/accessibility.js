function toggleClassForBody(className) {
   const body = $("body");
   const isClassPresent = body.hasClass(className);

   if (!isClassPresent) {
      body.addClass(className);
      sessionStorage.setItem(className, !isClassPresent);
   } else {
      body.removeClass(className);
      sessionStorage.removeItem(className);
   }
}

function changeFontSize(size) {
   const html = $("html");
   const currentSize = html.css("font-size");
   const newSize = parseInt(currentSize) + size;

   const maxFontSize = 24;
   const minFontSize = 12;

   if (newSize + size > maxFontSize || newSize + size < minFontSize) return;
   html.css("font-size", newSize + "px");
   placeAccessibilityButton();
   sessionStorage.setItem("accessibility-font-size", newSize);
}

function toggleHighContrast() {
   const body = $("body");
   const isHighContrast = body.hasClass("accessibility-high-contrast");

   if (body.hasClass("accessibility-negative-high-contrast")) body.removeClass("accessibility-negative-high-contrast");
   if (!isHighContrast) {
      if (body.hasClass("accessibility-negative-high-contrast")) body.removeClass("accessibility-negative-high-contrast");
      body.addClass("accessibility-high-contrast");
      sessionStorage.setItem("accessibility-high-contrast", !isHighContrast);
   } else {
      body.removeClass("accessibility-high-contrast");
      sessionStorage.removeItem("accessibility-high-contrast");
   }
}

const jumpToMenu = () => {
   jumpToElement('menu-assets');
};

const jumpToElement = (elementId) => {
   const element = document.getElementById(elementId);
   if (element) {
      element.focus();
   } else {
      console.error('Element with ID ' + elementId + ' not found.');
   }
};
document.getElementById('jump_menu').addEventListener('click', jumpToMenu);


function toggleNegativeHighContrast() {
   const body = $("body");
   const isHighContrast = body.hasClass("accessibility-negative-high-contrast");

   if (body.hasClass("accessibility-high-contrast")) body.removeClass("accessibility-high-contrast");
   if (!isHighContrast) {
      body.addClass("accessibility-negative-high-contrast");
      sessionStorage.setItem("accessibility-negative-high-contrast", !isHighContrast);
   } else {
      body.removeClass("accessibility-negative-high-contrast");
      sessionStorage.removeItem("accessibility-negative-high-contrast");
   }
}

function resetAllAccessibilityOptions() {
   const body = $("body");
   body.removeClass("accessibility-grayscale");
   sessionStorage.removeItem("accessibility-grayscale");

   body.removeClass("accessibility-high-contrast");
   sessionStorage.removeItem("accessibility-high-contrast");

   body.removeClass("accessibility-negative-high-contrast");
   sessionStorage.removeItem("accessibility-negative-high-contrast");

   body.removeClass("accessibility-link-underline");
   sessionStorage.removeItem("accessibility-link-underline");

   body.removeClass("accessibility-readable-font");
   sessionStorage.removeItem("accessibility-readable-font");

   $("html").css("font-size", "");
   sessionStorage.removeItem("accessibility-font-size");

   placeAccessibilityButton();
}

function placeAccessibilityButton() {
   const button = $("#accessibility-menu").find("button");
   const buttonWidth = button.outerWidth();

   button.css("left", -buttonWidth + "px");
}

$(document).ready(function () {
   if (sessionStorage.getItem("accessibility-font-size")) $("html").css("font-size", sessionStorage.getItem("accessibility-font-size") + "px");
   if (sessionStorage.getItem("accessibility-high-contrast")) $("body").addClass("accessibility-high-contrast");
   if (sessionStorage.getItem("accessibility-negative-high-contrast")) $("body").addClass("accessibility-negative-high-contrast");
   if (sessionStorage.getItem("accessibility-grayscale")) $("body").addClass("accessibility-grayscale");
   if (sessionStorage.getItem("accessibility-link-underline")) $("body").addClass("accessibility-link-underline");
   if (sessionStorage.getItem("accessibility-readable-font")) $("body").addClass("accessibility-readable-font");

   const menu = $("#accessibility-menu");
   menu.css("right", -menu.outerWidth() + "px");
   placeAccessibilityButton();
}
);
