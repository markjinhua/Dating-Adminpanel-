<?php

/*!
 * ifsoft.co.uk
 *
 * http://ifsoft.com.ua, https://ifsoft.co.uk, https://raccoonsquare.com
 * raccoonsquare@gmail.com
 *
 * Copyright 2012-2020 Demyanchuk Dmitry (raccoonsquare@gmail.com)
 */

class photo extends db_connect
{

    public $image = 0;
    private $mimetype;
    private $photoSize = 160;
    private $imgFilename = "";
    private $imageproperties = array();
    private $initialfilesize;

    private $imgData = 0;

    public function __construct($dbo, $imgFilename, $photoSize = 160)
    {

        parent::__construct($dbo);

        $this->imgFilename = $imgFilename;
        $this->photoSize = $photoSize;

        $this->initialfilesize = filesize($imgFilename);
        $this->imageproperties = getimagesize($imgFilename);
        $this->mimetype = image_type_to_mime_type($this->imageproperties[2]);

        if ($this->imageproperties[2] === IMAGETYPE_JPEG) {

            $this->imgData = imagecreatefromjpeg($this->imgFilename);

        } else {

            $this->imgData = imagecreatefrompng($this->imgFilename);
        }

        $srcW = $this->imageproperties[0];
        $srcH = $this->imageproperties[1];

        if ($srcW > $this->photoSize || $srcH > $this->photoSize) {

            if ($srcW < $srcH)  {

                $koe =  (int) $srcW / $this->photoSize;
                $destW =  (int) $this->photoSize;
                $destH =  (int) ceil($srcH/$koe);
                $src_x =  (int) ($destW / 2) - ($this->photoSize / 2);
                $src_y = 0;

            } else {

                $koe = (int) $srcH / $this->photoSize;
                $destH = (int) $this->photoSize;
                $destW = (int) ceil($srcW/$koe);
                $src_x = (int) ($destW / 2) - ($this->photoSize / 2);
                $src_y = 0;
            }

            $copy = @imagecreatetruecolor($destW, $destH);
            imagecopyresampled($copy, $this->imgData, 0, 0, 0, 0, $destW, $destH, imagesx($this->imgData), imagesy($this->imgData));
            imagedestroy($this->imgData);
            imageconvolution($copy, array
            ( // улучшаем четкость
                array(-1,-1,-1),
                array(-1,16,-1),
                array(-1,-1,-1)
            ), 8, 0);

            $this->imgData = imagecreatetruecolor($this->photoSize, $this->photoSize);
            imagecopy($this->imgData, $copy, 0, 0, $src_x, $src_y, $this->photoSize, $this->photoSize);
            imagedestroy($copy);
        }
    }

    public function getImgData()
    {

        return $this->imgData;
    }
}
