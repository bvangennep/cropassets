/* eslint no-unused-vars: 0*/
/* eslint object-shorthand: 0 */
/* eslint prefer-arrow-callback: 0 */
/* eslint func-names: 0 */
/* eslint no-var: 0 */
/* eslint no-alert: 0 */
/* eslint prefer-template: 0 */
/* global Craft */
/* global Garnish */
/* global $ */

/**
 * Initialize cropper
 *
 * @param  {String} handle
 * @param  {Number} elementId
 * @param  {Number} aspectRatio
 */
function initCropper(handle, elementId, aspectRatio) {
  Craft.postActionRequest('cropAssets/prepareForCrop', {
    elementId: elementId,
  }, function (response) {
    var modal = null;
    var source;

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
        modal = new Craft.ImageModal(Craft.ImageUpload.$modalContainerDiv, {
          postParameters: {},
          cropAction: 'cropAssets/applyCrop',
          onImageSave: function (resp) {
            Craft.cp.displayNotice(resp.message);
          },
        });

        modal.imageHandler = modal.settings;
      } else {
        modal.show();
      }

      modal.bindButtons();
      modal.addListener(modal.$saveBtn, 'click', 'saveImage');
      modal.addListener(modal.$cancelBtn, 'click', 'cancel');

      modal.removeListener(Garnish.Modal.$shade, 'click');

      Craft.ImageUpload.$modalContainerDiv.find('img').load(function () {
        var areaTool = new Craft.ImageAreaTool({
          aspectRatio: aspectRatio,
          initialRectangle: {
            mode: 'auto',
          },
        }, modal);
        areaTool.showArea();
        areaTool.containingModal.source = handle + ':' + elementId;
        modal.cropAreaTool = areaTool;
      });
    }
  });
}
