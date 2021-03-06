<?php

namespace Craft;

/**
 * Crop Assets Plugin.
 *
 * Adds the ability to manually crop and resize assets per field.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2016, Nerds & Company
 * @license   MIT
 *
 * @link      http://www.nerds.company
 */
class CropAssetsPlugin extends BasePlugin
{
    /**
     * Get plugin name.
     *
     * @return string
     */
    public function getName()
    {
        return Craft::t('Crop Assets');
    }

    /**
     * Get plugin description.
     *
     * @return string
     */
    public function getDescription()
    {
        return Craft::t('Adds the ability to manually crop and resize assets per field.');
    }

    /**
     * Get plugin version.
     *
     * @return string
     */
    public function getVersion()
    {
        return '2.1.0';
    }

    /**
     * Get plugin developer.
     *
     * @return string
     */
    public function getDeveloper()
    {
        return 'Nerds & Company';
    }

    /**
     * Get plugin developer url.
     *
     * @return string
     */
    public function getDeveloperUrl()
    {
        return 'https://github.com/nerds-and-company';
    }

    /**
     * @return array
     */
    public function defineSettings()
    {
        return array(
            'assetSource' => AttributeType::String,
        );
    }

    /**
     * @return string
     */
    public function getSettingsHtml()
    {
        return craft()->templates->render('cropAssets/_settings.twig', array(
            'settings' => $this->getSettings(),
        ));
    }

    /**
     * Listen for deletion of relations
     */
    public function init()
    {
        craft()->on('elements.onBeforeDeleteElements', function (Event $event) {
            $elementIds = $event->params['elementIds'];
            $elementIdString = implode(',', $elementIds);

            $cropAssets = craft()->db->createCommand()
                ->from('cropassets')
                ->where('entryId in (:ids)')
                ->orWhere('sourceAssetId in (:ids)')
                ->bindParam(':ids', $elementIdString)
                ->queryAll();

            if (!empty($cropAssets)) {
                $cropAssetIds = array_column($cropAssets, 'id');
                $targetAssetIds = array_column($cropAssets, 'targetAssetId');

                CropAssetsRecord::model()->deleteAllByAttributes(['id' => $cropAssetIds]);
                craft()->assets->deleteFiles($targetAssetIds);
            }
        });
    }

    /**
     * Register the schematic AssetsField model forthe CropAssets field
     *
     * @return array
     */
    public function registerSchematicFieldModels()
    {
        return [
            'CropAssets' => 'NerdsAndCompany\Schematic\Models\AssetsField'
        ];
    }
}
