<?php

namespace Craft;

/**
 * Crop Assets Model.
 *
 * Adds the ability to manually crop and resize assets per field.
 *
 * @author    Bob Olde Hampsink <b.oldehampsink@nerds.company>
 * @copyright Copyright (c) 2016, Bob Olde Hampsink
 * @license   MIT
 *
 * @link      http://github.com/boboldehampsink
 *
 * @property int $id
 * @property string $handle
 * @property int $sourceId
 * @property object|array $settings
 */
class CropAssetsModel extends AssetFileModel
{
    /**
     * Get cropped image url.
     *
     * @param null|array $settings
     *
     * @return string
     */
    public function getUrl($settings = null)
    {
        // Get image
        $file = $this->getFile();

        // Do the cropping
        $this->applyCrop($file, $settings);

        // Get base64 of crop
        $base64 = $this->getBase64($file);

        // Get mime
        $mime = IOHelper::getMimeType($file);

        // Delete the temp image
        IOHelper::deleteFile($file);

        // Return base64 string
        return 'data:'.$mime.';base64,'.$base64;
    }

    // Protected Methods
    // =========================================================================

    /**
     * Get physical file.
     *
     * @return string
     */
    protected function getFile()
    {
        // Get asset source
        $source = $this->getSource();

        // Get asset source type
        $sourceType = $source->getSourceType();

        // Get local copy from asset source type
        $file = $sourceType->getLocalCopy($this);

        // Return file path
        return $file;
    }

    /**
     * Apply crop.
     *
     * @param string     $file
     * @param array|null $settings
     */
    protected function applyCrop($file, $settings)
    {
        // Get settings
        $settings = $this->getSettings($settings);

        // Get crop values
        list($x1, $x2, $y1, $y2) = $this->getCropValues($file);

        // Load image
        $image = craft()->images->loadImage($file);

        // Do the cropping
        $image->crop($x1, $x2, $y1, $y2);

        // Do we need to scale to fit?
        if ($settings['width']) {
            $image->scaleToFit($settings['width'], $settings['height'], false);
        }

        // Save image
        $image->saveAs($file);
    }

    /**
     * Get settings for crop.
     *
     * @param array|null $settings
     *
     * @return array
     */
    protected function getSettings($settings)
    {
        // Get settings
        if (is_null($settings)) {
            $settings = (array) $this->settings;
            $settings['width'] = $this->settings['scaleToFitWidth'];
            $settings['height'] = $this->settings['scaleToFitHeight'];
        }

        // Height is optional, but should be set
        if (!isset($settings['height'])) {
            $settings['height'] = null;
        }

        return $settings;
    }

    /**
     * Get crop values and make up for CP fitting.
     *
     * @param string $file
     *
     * @return array
     */
    protected function getCropValues($file)
    {
        // Get saved crop values from db
        list($x1, $x2, $y1, $y2) = $this->getContent()->{$this->handle};

        // Get original image size
        list($width, $height) = ImageHelper::getImageSize($file);

        // Calculate factor
        $factor = $width / 500;

        // Return fixed crop values
        return array($x1 * $factor, $x2 * $factor, $y1 * $factor, $y2 * $factor);
    }

    /**
     * Get base64 of file.
     *
     * @param string $file
     *
     * @return string
     */
    protected function getBase64($file)
    {
        // Load cropped file
        $crop = IOHelper::getFileContents($file);

        // Base64 encode it
        return base64_encode($crop);
    }

    /**
     * {@inheritdoc} BaseModel::defineAttributes()
     *
     * @return array
     */
    protected function defineAttributes()
    {
        return array_merge(parent::defineAttributes(), array(
            'handle' => AttributeType::String,
            'settings' => AttributeType::Mixed,
        ));
    }
}
