var modalWindow;
var modalTask;

$(function() {
   modalWindow = $("<div></div>").dialog({
      title: __('Status'),
      resizable: true,
      width: '300',
      autoOpen: false,
      height: '150',
      modal: true,
      position: {
         my: 'center'
      },
      open: function( event, ui ) {
         //remove existing tinymce when reopen modal (without this, tinymce don't load on 2nd opening of dialog)
         modalWindow.find('.mce-container').remove();
      }
   });
});

var specialstatus = new function() {
 
    this.showStatusModal = function (idint) {
       var RegexUrl = /^(.*)front\/.*\.php/;
       var RegexUrlRes = RegexUrl.exec(window.location.pathname);
         id = idint.toString();
       modalWindow.load(
          RegexUrlRes[1]+'ajax/specialstatus.php?status=delete&id='+id
       ).dialog('open');
    }
} 