<?php
namespace Craft;

/**
 * Crop Assets Variable.
 *
 * Adds the ability to manually crop and resize assets per field.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2016, Nerds & Company
 * @license   MIT
 *
 * @link      http://www.nerds.company
 */
class CropAssetsVariable
{
    /**
     * Get asset source options for settings field
     *
     * @return array
     */
    public function getAssetSourceOptions()
    {
        $assetSources = craft()->assetSources->getAllSources();
        return array_map(array($this, 'getAssetSourceOption'), $assetSources);
    }

    /**
     * @param AssetSourceModel $source
     * @return array
     */
    private function getAssetSourceOption(AssetSourceModel $source)
    {
        return array(
            'label' => $source->name,
            'value' => $source->handle,
        );
    }
}
