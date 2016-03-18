<?php

namespace Craft;

/**
 * Crop Assets Controller.
 *
 * Adds the ability to manually crop and resize assets per field.
 *
 * @author    Bob Olde Hampsink <b.oldehampsink@nerds.company>
 * @copyright Copyright (c) 2016, Bob Olde Hampsink
 * @license   MIT
 *
 * @link      http://github.com/boboldehampsink
 */
class CropAssetsController extends BaseController
{
    /**
     * Prepare asset for cropping.
     */
    public function actionPrepareForCrop()
    {
        $this->requireAjaxRequest();
        $elementId = craft()->request->getParam('elementId');

        // Get the asset file
        $asset = craft()->assets->getFileById($elementId);
        $source = $asset->getSource();
        $sourceType = $source->getSourceType();
        $file = $sourceType->getLocalCopy($asset);

        try {
            // Test if we will be able to perform image actions on this image
            if (!craft()->images->checkMemoryForImage($file)) {
                IOHelper::deleteFile($file);
                $this->returnErrorJson(Craft::t('The selected image is too large.'));
            }

            // Scale to fit 500x500 for fitting in CP modal
            craft()->images->
                loadImage($file)->
                scaleToFit(500, 500, false)->
                saveAs($file);

            list($width, $height) = ImageHelper::getImageSize($file);

            // If the file is in the format badscript.php.gif perhaps.
            if ($width && $height) {
                $html = craft()->templates->render('_components/tools/cropper_modal',
                    array(
                        'imageUrl' => $asset->url,
                        'width' => $width,
                        'height' => $height,
                        'fileName' => $asset->filename,
                    )
                );

                $this->returnJson(array('html' => $html));
            }
        } catch (Exception $exception) {
            $this->returnErrorJson($exception->getMessage());
        }
    }

    /**
     * Crop asset.
     */
    public function actionApplyCrop()
    {
        $this->requireAjaxRequest();

        try {
            $x1 = craft()->request->getRequiredPost('x1');
            $x2 = craft()->request->getRequiredPost('x2');
            $y1 = craft()->request->getRequiredPost('y1');
            $y2 = craft()->request->getRequiredPost('y2');
            $source = craft()->request->getRequiredPost('source');

            // Get element id and field handle
            list($handle, $elementId) = explode(':', $source);

            // Get asset
            $asset = craft()->assets->getFileById($elementId);

            // Save values on asset
            $asset->getContent()->$handle = array($x1, $x2, $y1, $y2);
            craft()->content->saveContent($asset, false);

            // Return with success message
            $this->returnJson(array('message' => Craft::t('Successfully saved crop.')));
        } catch (Exception $exception) {
            $this->returnErrorJson($exception->getMessage());
        }

        $this->returnErrorJson(Craft::t('Something went wrong when processing the crop.'));
    }
}
