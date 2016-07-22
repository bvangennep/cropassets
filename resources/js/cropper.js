/* eslint no-unused-vars: 0*/
/* eslint object-shorthand: 0 */
/* eslint prefer-arrow-callback: 0 */
/* eslint func-names: 0 */
/* eslint no-var: 0 */
/* eslint no-alert: 0 */
/* eslint prefer-template: 0 */
/* eslint no-new: 0 */
/* global Craft */
/* global Garnish */
/* global Cropper */
/* global $ */

var CropAssets = {
  /**
   * Initialize cropper
   *
   * @param  {String} handle
   * @param  {Number} elementId
   * @param  {Number} aspectRatio
   */
  initCropper: function(elementId, aspectRatio) {
    Craft.postActionRequest('cropAssets/prepareForCrop', {
      elementId: elementId,
    }, function (response) {
      var modal = null;
      var image;
      var cropper;

      if (response.error) {
        alert(response.error);
        return;
      }

      if (Craft.ImageUpload.$modalContainerDiv === null) {
        Craft.ImageUpload.$modalContainerDiv = $('<div class="modal fitted"></div>')
          .addClass('cp-image-modal')
          .appendTo(Garnish.$bod);
      }

      if (response.html) {
        Craft.ImageUpload.$modalContainerDiv.empty().append(response.html);

        if (!modal) {
          modal = new Craft.ImageModal(Craft.ImageUpload.$modalContainerDiv, {});
          modal.imageHandler = modal.settings;
        } else {
          modal.show();
        }

        // Initialize
        image = Craft.ImageUpload.$modalContainerDiv.find('img');
        cropper = new Cropper(image[0], {
          aspectRatio: aspectRatio,
          movable: false,
          zoomable: false,
          rotatable: false,
          data: response.settings,
        });

        modal.bindButtons();

        // Save cropped image
        modal.addListener(modal.$saveBtn, 'click', function () {
          cropper.getCroppedCanvas().toBlob(function (blob) {
            var formData = new FormData();
            formData.append(Craft.csrfTokenName, Craft.csrfTokenValue);
            formData.append('croppedImage', blob, response.filename);
            formData.append('elementId', elementId);
            formData.append('settings', JSON.stringify(cropper.getData(true)));

            $.ajax('/actions/cropAssets/applyCrop', {
              method: 'POST',
              data: formData,
              processData: false,
              contentType: false,
              success: function (resp) {
                Craft.cp.displayNotice(resp.message);
                modal.hide();
              },
              error: function (resp) {
                Craft.cp.displayNotice(resp.message);
              },
            });
          });
        });
        modal.addListener(modal.$cancelBtn, 'click', 'cancel');

        modal.removeListener(Garnish.Modal.$shade, 'click');
      }
    });
  },

  applyContextMenu: function () {
    var $elements = $('.cropassets .element');
    $elements.each(function () {
      var elementId = $(this).data('id');
      var menuOptions = [{
        label: Craft.t('Crop asset'),
        onClick: function () {
          CropAssets.initCropper(elementId, 'aspectRation');
        },
      }];
      new Garnish.ContextMenu(this, menuOptions, { menuClass: 'menu' });
    });
  },
};

$(function () {
  CropAssets.applyContextMenu();
});
