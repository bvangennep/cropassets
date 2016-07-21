<?php

namespace Craft;

/**
 * Crop Assets Fieldtype.
 *
 * Adds the ability to manually crop and resize assets per field.
 *
 * @author    Bob Olde Hampsink <b.oldehampsink@nerds.company>
 * @copyright Copyright (c) 2016, Bob Olde Hampsink
 * @license   MIT
 *
 * @link      http://github.com/boboldehampsink
 */
class CropAssetsFieldType extends AssetsFieldType
{
    /**
     * Template to use for field rendering.
     *
     * @var string
     */
    protected $inputTemplate = 'cropAssets/_input';

    /**
     * Get fieldtype name.
     *
     * @return string
     */
    public function getName()
    {
        return Craft::t('Crop Assets');
    }

    /**
     * We're going to save an array crop values.
     *
     * @return mixed
     */
    public function defineContentAttribute()
    {
        return AttributeType::Mixed;
    }

    /**
     * Return criteria in back-end and cropped images in front-end.
     *
     * @param mixed $value
     *
     * @return CropAssetsModel
     */
    public function prepValue($value)
    {
        // Behave as normal asset in back-end
        if (craft()->request->isCpRequest()) {
            return $this->prepValueForCp($value);
        }

        return $this->prepValueForSite($value);
    }

    /**
     * Override default asset settings - leaving fileKinds out.
     *
     * @return string|null
     */
    public function getSettingsHtml()
    {
        // Create a list of folder options for the main Source setting, and source options for the upload location
        // settings.
        $folderOptions = array();
        $sourceOptions = array();
        $sources = (array) $this->getElementType()->getSources();
        foreach ($sources as $key => $source) {
            if (!isset($source['heading'])) {
                $folderOptions[] = array('label' => $source['label'], 'value' => $key);
            }
        }
        foreach (craft()->assetSources->getAllSources() as $source) {
            $sourceOptions[] = array('label' => $source->name, 'value' => $source->id);
        }
        $namespace = craft()->templates->getNamespace();
        $isMatrix = (strncmp($namespace, 'types[Matrix][blockTypes][', 26) === 0);

        return craft()->templates->render('cropAssets/_settings', array(
            'folderOptions' => $folderOptions,
            'sourceOptions' => $sourceOptions,
            'targetLocaleField' => $this->getTargetLocaleFieldHtml(),
            'settings' => $this->getSettings(),
            'type' => $this->getName(),
            'isMatrix' => $isMatrix,
        ));
    }

    // Protected
    // =========================================================================

    /**
     * Let users know we're uploading an image.
     *
     * @return string
     */
    protected function getAddButtonLabel()
    {
        return Craft::t('Add an image');
    }

    /**
     * Returns an array of variables that should be passed to the input template.
     *
     * @param string $name
     * @param mixed  $criteria
     *
     * @return array
     */
    protected function getInputTemplateVariables($name, $criteria)
    {
        $variables = parent::getInputTemplateVariables($name, $criteria);
        $variables['aspectRatio'] = $this->getSettings()->aspectRatio;

        return $variables;
    }

    /**
     * Limit filekinds to image only.
     *
     * @return array
     */
    protected function defineSettings()
    {
        return array_merge(parent::defineSettings(), array(
            'restrictFiles' => array(AttributeType::Bool, 'default' => true),
            'allowedKinds' => array(AttributeType::Mixed, 'default' => array('image')),
            'limit' => array(AttributeType::Number, 'default' => 1),
            'scaleToFitWidth' => AttributeType::Number,
            'scaleToFitHeight' => AttributeType::Number,
            'aspectRatio' => array(AttributeType::Number, 'decimals' => 2),
        ));
    }

    // Private
    // =========================================================================

    /**
     * Prep value for CP.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    private function prepValueForCp($value)
    {
        // Overwrite value, if any
        if ($value) {

            // Unset value
            $value = null;

            // Fetch target id(s)
            $results = craft()->db->createCommand()
                                ->select('targetId')
                                ->from('relations')
                                ->where(array(
                                    'fieldId' => $this->model->id,
                                    'sourceId' => $this->element->id,
                                ))
                                ->queryAll();

            // If db result is valid
            if ($results && is_array($results)) {

                // Gather value
                $value = array();

                // Loop through target ids
                foreach ($results as $result) {
                    $value[] = $result['targetId'];
                }
            }
        }

        // Return with new values
        return parent::prepValue($value);
    }

    /**
     * Prep value for site.
     *
     * @param mixed $value
     *
     * @return CropAssetsModel|null
     */
    private function prepValueForSite($value)
    {
        $targetAssets = [];
        foreach ($value as $sourceAssetId) {
            $cropAssets = craft()->cropAssets->getCropAssetsModelBySource($sourceAssetId);
            if ($cropAssets && $cropAssets->targetAssetId) {
                $targetAssets[] = $cropAssets->targetAssetId;
            } else {
                $targetAssets[] = $sourceAssetId;
            }
        }
        return parent::prepValue($targetAssets);
    }
}
