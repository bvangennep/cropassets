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
        $folder = craft()->cropAssets->getAssetSource();
        if ($folder === null) {
            $this->returnErrorJson(Craft::t('No asset source has been configured for cropped assets.'));
        }

        // Get the asset file
        $asset = craft()->assets->getFileById($elementId);
        $source = $asset->getSource();
        $sourceType = $source->getSourceType();
        $cropAssets = craft()->cropAssets->getCropAssetsModelBySource($elementId);

        try {
            // If the file is in the format badscript.php.gif perhaps.
            if ($asset->getWidth()) {
                list($width, $height) = craft()->cropAssets->getCropModalSize($asset);

                $html = craft()->templates->render('_components/tools/cropper_modal',
                    array(
                        'imageUrl' => $asset->url,
                        'width' => $width,
                        'height' => $height,
                        'fileName' => $asset->filename,
                    )
                );

                $this->returnJson(array(
                    'html' => $html,
                    'filename' => $asset->filename,
                    'settings'=> $cropAssets->settings,
                ));
            }
        } catch (Exception $exception) {
            $this->returnErrorJson($exception->getMessage());
        }
    }

    /**
     * Save cropped asset
     */
    public function actionApplyCrop()
    {
        $this->requireAjaxRequest();
        $elementId = craft()->request->getRequiredPost('elementId');
        $settings = craft()->request->getRequiredPost('settings');
        $folder = craft()->cropAssets->getAssetSource();
        if ($folder === null) {
            $this->returnErrorJson(Craft::t('No asset source has been configured for cropped assets.'));
        }

        $assetOperationResult = craft()->cropAssets->uploadCroppedAsset($folder);
        if ($assetOperationResult->isSuccess()) {
            $cropAssets = craft()->cropAssets->getCropAssetsModelBySource($elementId);
            $cropAssets->sourceAssetId = $elementId;
            $cropAssets->targetAssetId = $assetOperationResult->getDataItem('fileId');
            $cropAssets->settings = $settings;

            craft()->cropAssets->saveCropAssets($cropAssets);

            $this->returnJson(array('message' => Craft::t('Successfully saved crop.')));
        }
        $this->returnErrorJson(Craft::t($assetOperationResult->errorMessage));
    }
}
