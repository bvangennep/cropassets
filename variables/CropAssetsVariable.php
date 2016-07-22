<?php
namespace Craft;

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
