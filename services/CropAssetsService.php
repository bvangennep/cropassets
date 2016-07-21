<?php

namespace Craft;

/**
 * Crop Assets Service.
 *
 * Adds the ability to manually crop and resize assets per field.
 *
 * @author    Bob Olde Hampsink <b.oldehampsink@nerds.company>
 * @copyright Copyright (c) 2016, Bob Olde Hampsink
 * @license   MIT
 *
 * @link      http://github.com/boboldehampsink
 */
class CropAssetsService extends BaseApplicationComponent
{
    /**
     * Get a CropAssetsmodel by sourceAssetId, or new if it does not exist
     *
     * @param  int $sourceAssetId
     * @return CropAssetsModel
     */
    public function getCropAssetsModelBySource($sourceAssetId)
    {
        $model = new CropAssetsModel();
        $record = CropAssetsRecord::model()->findByAttributes(['sourceAssetId' => $sourceAssetId]);
        if ($record) {
            $model = CropAssetsModel::populateModel($record);
        }

        return $model;
    }

    /**
     * Save a crop assets record
     *
     * @param  CropAssetsModel $cropAssets
     * @return bool
     */
    public function saveCropAssets(CropAssetsModel $cropAssets)
    {
        $record = CropAssetsRecord::model()->findByAttributes(['id' => $cropAssets->id]);
        if ($record) {
            // Delete old cropped image
            craft()->assets->deleteFiles([$record->targetAssetId]);
        } else {
            $record = new CropAssetsRecord();
        }

        $record->setAttributes($cropAssets->getAttributes());
        $record->settings = $cropAssets->settings;

        return $record->save();
    }

    /**
     * Upload a cropped asset
     * @TODO: Get folderID from plugin settings
     *
     * @return AssetOperationResponseModel
     */
    public function uploadCroppedAsset()
    {
        $folderId = 2;
        $uploadedFile = UploadedFile::getInstanceByName('croppedImage');
        if ($uploadedFile) {
            $assetOperationResult = craft()->assets->insertFileByLocalPath($uploadedFile->tempName, $uploadedFile->name, $folderId, AssetConflictResolution::KeepBoth);
        } else {
            $assetOperationResult = new AssetOperationResponseModel();
            $assetOperationResult->setsetError('No source image was found');
        }

        return $assetOperationResult;
    }
}
