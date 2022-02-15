<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Photo
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Photo extends File
{


    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="photos")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     *
     */
    protected $user;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_main", type="boolean")
     */
    private $isMain;

    /**
     * Set isMain
     *
     * @param boolean $isMain
     * @return Photo
     */
    public function setIsMain($isMain)
    {
        $this->isMain = $isMain;

        return $this;
    }



    /**
     * Get isMain
     *
     * @return boolean
     */
    public function getIsMain()
    {
        return $this->isMain;
    }

    public function getUploadDir()
    {
        return '/media/photos/' . $this->user->getId();
    }

    public function getFaceAbsolutePath(){
        //$this->setIdIfDeleted();
        return $_SERVER['DOCUMENT_ROOT'] . $this->getUploadDir() . '/' . $this->id . '-face.' . $this->ext;
    }

    public function getFaceWebPath($with_photo_version = true)
    {
        $faceWebPath = $this->getUploadDir() . '/' . $this->id . '-face.' . $this->ext;
        $photo = is_file($_SERVER['DOCUMENT_ROOT'] . $faceWebPath) && filesize($_SERVER['DOCUMENT_ROOT'] . $faceWebPath) > 0 ? $faceWebPath : $this->getWebPath();
        if ($with_photo_version) {
            $photo = $photo . '?r=' . $this->updated;
        }
        return $photo;
    }

    public function getNoFile()
    {
        return $this->user->getNoPhoto();
    }

    /**
     * Called before entity removal
     *
     * @ORM\PostRemove()
     */
    public function removeUpload()
    {
        parent::removeUpload();

        $file = $this->getFaceAbsolutePath();
        if (null !== $file && is_file($file)) {
            unlink($file);
        }
    }

    public function detectFace($hostName)
    {
        $api_key = 'AIzaSyDzvtLRGb0ZjgIHZ8GFTZrhDe3fvRTsai8';
        $cvurl = 'https://vision.googleapis.com/v1/images:annotate?key=' . $api_key;
        $type = 'FACE_DETECTION';
        $photoUrl = 'http://' . $hostName . $this->getWebPath();
        $data = file_get_contents($photoUrl);
        $original_photo_width = getimagesizefromstring($data)[0];
        $original_photo_height = getimagesizefromstring($data)[1];
        $base64 = base64_encode($data);
        $request_json = '
            {
                "requests": [
                    {
                        "image": {
                            "content":"' . $base64 . '"
                        },
                        "features": [
                            {
                                "type": "' . $type . '",
                                "maxResults": 200
                            }
                        ]
                    }
                ]
            }';

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $cvurl);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $request_json);
        $json_response = curl_exec($curl);
        curl_close($curl);
        $result = (json_decode($json_response, true));

//        if ($isTest) {
//            var_dump($result);die;
//        }


        /*print_r($result['responses'][0]['faceAnnotations'][0]['boundingPoly']['vertices']);
        die;*/


        if(!isset($result['responses'][0]['faceAnnotations'])){
            return null;
        }

        $vertices = $result['responses'][0]['faceAnnotations'][0]['boundingPoly']['vertices'];
        $topLeft = $vertices[0];
        $topRight = $vertices[1];

        //if()

        if(!isset($topRight['x'])){
            $topRight['x'] = 0;
        }

        if(!isset($topLeft['x'])){
            $topLeft['x'] = 0;
        }

        if($topRight['x'] == 0 && $topLeft['x'] > 0){
            $size = getimagesize($photoUrl);
            $squareSideLength = $size[0] - $topLeft['x'];
        }
        else{
            $squareSideLength = $topRight['x'] - $topLeft['x'];
        }

        $faceW = $squareSideLength  + 40 < $original_photo_width ? $squareSideLength  + 40 : $original_photo_width;
        $faceH = $squareSideLength  + 40 < $original_photo_height ? $squareSideLength + 80 : $original_photo_height;

        if($faceH == $original_photo_height && $faceW == $original_photo_width) {
            return null;
        }


        return array(
            'x' => isset($topLeft['x']) ? abs($topLeft['x'] - 20) : 0,
            'y' => isset($topLeft['y']) ? abs($topLeft['y'] - 20) : 0,
//            'y' => $topLeft['y'],
            'w' => $faceW,
            'h' => $faceH,
//            'x' =>  $topLeft['x'],
//            'y' =>  $topLeft['y'],
//            'w' => $squareSideLength,
//            'h' => $squareSideLength,
        );

    }








    public function testDetectFace($hostName)
    {
        $api_key = 'AIzaSyDzvtLRGb0ZjgIHZ8GFTZrhDe3fvRTsai8';
        $cvurl = 'https://vision.googleapis.com/v1/images:annotate?key=' . $api_key;
        $type = 'FACE_DETECTION';
        $photoUrl = 'http://' . $hostName . $this->getWebPath();
        $data = file_get_contents($photoUrl);
        $original_photo_width = getimagesizefromstring($data)[0];
        $original_photo_height = getimagesizefromstring($data)[1];
        $base64 = base64_encode($data);
        $request_json = '
            {
                "requests": [
                    {
                        "image": {
                            "content":"' . $base64 . '"
                        },
                        "features": [
                            {
                                "type": "' . $type . '",
                                "maxResults": 200
                            }
                        ]
                    }
                ]
            }';

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $cvurl);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $request_json);
        $json_response = curl_exec($curl);
        curl_close($curl);
        $result = (json_decode($json_response, true));


//        var_dump($result);die;
        if(!isset($result['responses'][0]['faceAnnotations'])){
            return null;
        }

        $vertices = $result['responses'][0]['faceAnnotations'][0]['boundingPoly']['vertices'];

//        var_dump($vertices);die;
        $hints = array(
            'TL' => $vertices[0], //TL = TopLeft
            'TR' => $vertices[1], //TR = TopRight
            'BR' => $vertices[2], //BR = BottomRight
            'BL' => $vertices[3], //BL = BottomLeft
        ); // НОРМАЛЬНОЕ ЛИЦО, НЕ ОБРЕЗОК
//
//        var_dump($hints);

        if (!isset($hints['TL']['y'])) {
            $hints['TL']['y'] = 0;
//            var_dump(1);
        }
        if (!isset($hints['TL']['x'])) {
            $hints['TL']['x'] = 0;
//            var_dump(2);
        }

        if (!isset($hints['TR']['y'])) {
            $hints['TR']['y'] = 0;
//             var_dump(3);
        }
        if (!isset($hints['TR']['x'])) {
            $hints['TR']['x'] = 0;
//             var_dump(4);
        }


        if (!isset($hints['BR']['y'])) {
            $hints['BR']['y'] = 0;
//             var_dump(5);
        }
        if (!isset($hints['BR']['x'])) {
            $hints['BR']['x'] = 0;
//             var_dump(6);
        }


        if (!isset($hints['BL']['y'])) {
            $hints['BL']['y'] = 0;
//             var_dump(7);
        }
        if (!isset($hints['BL']['x'])) {
            $hints['BL']['x'] = 0;
//             var_dump(8);
        }

        $heightW = abs((int)$hints['BL']['y'] - (int)$hints['TL']['y']);
        $widthtW = abs((int)$hints['BR']['x'] - (int)$hints['BL']['x']);
        if ($heightW == 0) {
            $heightW = $original_photo_height;
        }

        if ($widthtW == 0) {
            $widthtW = $original_photo_width;
        }
//        var_dump($heightW, $widthtW);die;

        return array(
            'x' => $hints['TL']['x'],
            'y' => $hints['TR']['y'],
            'h' => $heightW,
            'w' => $widthtW,
        );
//        return array(
//            'x' => isset($topLeft['x']) ? abs($topLeft['x'] - 20) : 0,
//            'y' => isset($topLeft['y']) ? abs($topLeft['y'] - 20) : 0,
////            'y' => $topLeft['y'],
//            'w' => $faceW,
//            'h' => $faceH,
////            'x' =>  $topLeft['x'],
////            'y' =>  $topLeft['y'],
////            'w' => $squareSideLength,
////            'h' => $squareSideLength,
//        );
//        var_dump($widthtW, $hints);die;
//
//        var_dump($result['responses'][0]);die;

    }






    public function rotate($rotate = 90){
        //$this->contener
//        $f = fopen($path, 'w');
//        fwrite($f, $photo);
//        fclose($f);

        //$degrees = 180;

// Content type
        chmod($this->getAbsolutePath(),0777);
        if ($this->ext == 'jpg' || $this->ext == 'jpeg') {
            chmod($this->getAbsolutePath(),0777);
            header('Content-type: image/jpeg');
            $source = imagecreatefromjpeg($this->getAbsolutePath());
            $rotateSorce = imagerotate($source, $rotate, 0);
            $res = imagejpeg($rotateSorce, $this->getAbsolutePath(), 100);
            imagedestroy($source);
            imagedestroy($rotateSorce);

            chmod($this->getAbsolutePath(),0777);
            header('Content-type: image/jpeg');
            $source = imagecreatefromjpeg($this->getFaceAbsolutePath());
            $rotateSorce = imagerotate($source, $rotate, 0);
            $res = imagejpeg($rotateSorce, $this->getFaceAbsolutePath(), 100);
            imagedestroy($source);
            imagedestroy($rotateSorce);
//            var_dump($this->getFaceAbsolutePath());
        } elseif ($this->ext == 'png') {
            header('Content-type: image/png');
            $source = imagecreatefrompng($this->getAbsolutePath());
            $bgColor = imageColorAllocateAlpha($source, 0, 0, 0, 0);
            $rotateSorce = imagerotate($source, $rotate, $bgColor);
            imagealphablending($rotateSorce, false);
            imagesavealpha($rotateSorce, true);
            imagepng($rotateSorce, $this->getAbsolutePath());
            imagedestroy($source);
            imagedestroy($rotateSorce);

            //face
            var_dump($this->getAbsolutePath());
            $faceSource = imagecreatefrompng($this->getFaceAbsolutePath());
            $bgColor = imageColorAllocateAlpha($faceSource, 0, 0, 0, 0);
            $faceRotateSorce = imagerotate($faceSource, $rotate, $bgColor);
            imagealphablending($faceRotateSorce, false);
            imagesavealpha($faceRotateSorce, true);
            imagepng($faceRotateSorce, $this->getAbsolutePath());
            imagedestroy($faceSource);
            imagedestroy($faceRotateSorce);
        }
        return $this;
    }

//    public function getMainPhoto() {
//        return $this->getUser()->getMainPhoto();
//    }

    public function changePhotoSize($width, $photoPath){
        chmod($photoPath,0777);
        $dimensions = getimagesize($photoPath);
        $width_old = $dimensions[0];
        $height_old = $dimensions[1];
        if ($width < $width_old) {
            $new_width = $width;
            $new_height = ceil($height_old * ($width / $width_old));
        }else{
            return false;
        }

        if ($this->ext == 'jpg' || $this->ext == 'jpeg') {

            $original = imagecreatefromjpeg($photoPath); // ORIGINAL 600 X 324 px
            // (B) EMPTY CANVAS WITH REQUIRED DIMENSIONS
            // imagecreatetruecolor(WIDTH, HEIGHT)
            $resized = imagecreatetruecolor($new_width, $new_height); // SMALLER BY 50%

            // (C) RESIZE THE IMAGE
            // imagecopyresampled(TARGET, SOURCE, TX, TY, SX, SY, TWIDTH, THEIGHT, SWIDTH, SHEIGHT)
            imagecopyresampled($resized, $original, 0, 0, 0, 0, $new_width, $new_height, $width_old, $height_old);

            // (D) SAVE/OUTPUT RESIZED IMAGE
            imagejpeg($resized, $photoPath);

            // (E) OPTIONAL - CLEAN UP
            imagedestroy($original);
            imagedestroy($resized);

        } elseif ($this->ext == 'png') {

            $original = imagecreatefrompng($photoPath); // ORIGINAL 450 X 600 px

            // (B) EMPTY CANVAS WITH REQUIRED DIMENSIONS
            $resized = imagecreatetruecolor($new_width, $new_height); // SMALLER BY 50%

            // (C) PREFILL WITH TRANSPARENT LAYER
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            imagefilledrectangle(
                $resized, 0, 0, 225, 300,
                imagecolorallocatealpha($resized, 255, 255, 255, 127)
            );

            // (D) RESIZE THE IMAGE
            imagecopyresampled($resized, $original, 0, 0, 0, 0, $new_width ,    $new_height, $width_old, $height_old);

            // (E) SAVE/OUTPUT RESIZED IMAGE
            imagepng($resized, $photoPath);

            // (F) OPTIONAL - CLEAN UP
            imagedestroy($original);
            imagedestroy($resized);

        }
        return true;
    }

    public function resize($width = 800) {
        return $this->changePhotoSize($width, $this->getAbsolutePath());
    }

    public function resizeFace($width = 500) {
        $facePath = $this->getFaceAbsolutePath();
        return (is_file($facePath) && filesize($facePath) > 0) ? $this->changePhotoSize($width, $facePath) : false;
    }
}
