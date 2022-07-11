<?php
namespace IkechukwuOkalia;
use Intervention\Image\Gd\Font,
    Intervention\Image\Gd\Shapes\CircleShape,
    Intervention\Image\ImageManager;

class LetterAvatar {
    /**
   * Image Type PNG
   */
  const MIME_TYPE_PNG = 'image/png';

  /**
   * Image Type JPEG
   */
  const MIME_TYPE_JPEG = 'image/jpeg';

  /**
   * @var string
   */
  protected $_name;
    /**
   * @var string
   */
  protected $_name_initials;
  /**
   * @var string
   */
  protected $_shape;
  /**
   * @var int
   */
  protected $_size;
  protected $_length;

  /**
   * @var ImageManager
   */
  private $_imgMan;

  /**
   * @var string
   */
  private $_bg_color;

  /**
   * @var string
   */
  private $_fg_Color;
  /**
   * LetterAvatar constructor.
   * @param string $name
   * @param string $shape
   * @param int    $size
   * @param int    $length >= 1 | <= 5. Default = 2
   */

  function __construct (string $name, string $shape = "circle", int $size = 48, int $length = 2) {
    $this->_setName($name);
    $this->_setImgMan(new ImageManager());
    $this->setShape($shape);
    $this->setSize($size);
    $this->setLength($length);
  }
    /**
   * color in RGB format (example: #FFFFFF)
   * 
   * @param $bg_color
   * @param $fg_color
   */
  public function setColor(string $bg_color, string $fg_color) {
    $this->_bg_color = $bg_color;
    $this->_fg_Color = $fg_color;
    return $this;
  }
  /**
   * @param string $name
   */
  protected final function _setName(string $name) {
    $this->_name = $name;
  }
  /**
   * @param ImageManager $imageManager
   */
  private function _setImgMan(ImageManager $imageManager) {
    $this->_imgMan = $imageManager;
  }
  /**
   * @param string $shape
   */
  public function setShape(string $shape) {
    $this->_shape = $shape;
  }
  /**
   * @param int $size
   */
  public function setSize(int $size) {
    $this->_size = $size;
  }
  public function setLength(int $length) {
    $this->_length = ($length > 0 && $length <= 5) ? $length : 1;
  }
  /**
   * @return \Intervention\Image\Image
   */
  private function _generate(): \Intervention\Image\Image {
    $is_circle = $this->_shape === 'circle';

    $this->_name_initials = $this->getInitials($this->_name);
    $this->_bg_color = $this->_bg_color ?: $this->_stringToColor($this->_name);
    $this->_fg_Color = $this->_fg_Color ?: '#fafafa';

    $canvas = $this->_imgMan->canvas(480, 480, $is_circle ? null : $this->_bg_color);

    if ($is_circle) {
      $canvas->circle(480, 240, 240, function (CircleShape $draw) {
        $draw->background($this->_bg_color);
      });
    }

    $canvas->text($this->_name_initials, 240, 240, function (Font $font) {
      $font->file(__DIR__ . '/fonts/arial-bold.ttf');
      $font->size($this->_fontSize($this->_length));
      $font->color($this->_fg_Color);
      $font->valign('middle');
      $font->align('center');
    });

    return $canvas->resize($this->_size, $this->_size);
  }
  private function _fontSize ($length) {
    $sizes = [
      1 => 280,
      2 => 200,
      3 => 150,
      4 => 120,
      5 => 100
    ];
    return \array_key_exists($length, $sizes) ? $sizes[$length] : null;
  }
    /**
   * @param string $name
   * @return string
   */
  public function getInitials(string $name): string {
    $name_parts = $this->_breakName($name);

    if(!$name_parts || \strlen($name) < $this->_length) {
      return '';
    }
    $intitals = "";
    if (\count($name_parts) >= $this->_length) {
      foreach ($name_parts as $part) {
        if (\strlen($intitals) < $this->_length) $intitals .= $this->_getLetter($part, 0);
      }
    } else {
      $parts = [];
      foreach ($name_parts as $part) {
        if (\count($parts) < $this->_length) $parts[] = $this->_getLetter($part, 0, 1);
      }
      // get the remaining letters
      $rem = $this->_length - \count($parts);
      // echo $rem;
      for ($i=0; $i < $rem; $i++) { 
        if ($i < \count($parts)) {
          $pos = \ceil(($i + 1)/\count($name_parts));
          $parts[$i] = $parts[$i] . $this->_getLetter($name_parts[$i], $pos, 1);
        }
      }
      $rem = $this->_length - \strlen(\implode("", $parts));
      if ($rem > 0) {
        for ($i=0; $i < $rem; $i++) { 
          if ($i < \count($parts)) {
            $pos = \ceil(($i + 3)/ \count($name_parts));
            $parts[$i] = $parts[$i] . $this->_getLetter($name_parts[$i], $pos, 1);
          }
        }
      }
      $intitals = \implode("", $parts);
    }
    return $intitals;
  }
  /**
   * @param string $word
   * @param int $pos
   * @return string
   */
  private function _getLetter(string $word, int $pos = 0, int $len = 1): string {
    return \strlen(\trim($word)) < $pos ? "" : \mb_strtoupper(\trim(\mb_substr($word, $pos, $len, 'UTF-8')));
  }
  /**
   * Explodes Name into an array.
   * The function will check if a part is , or blank
   *
   * @param string $name Name to be broken up
   * @return array Name broken up to an array
   */
  private function _breakName (string $name): array {
    $words = \explode(' ', $name);
    $words = \array_filter($words, function($word) {
      return $word !=='' && $word !== ',';
    });
    return \array_values($words);
  }
  /**
   * Get the generated Letter-Avatar as a png or jpg string
   *
   * @param string $mimetype
   * @param int    $quality
   * @return string
   */
  public function encode(string $mimetype = self::MIME_TYPE_PNG, int $quality = 90): string {
    $allowedMimeTypes = [
      self::MIME_TYPE_PNG,
      self::MIME_TYPE_JPEG,
    ];
    if(!in_array($mimetype, $allowedMimeTypes, true)) {
        throw new \InvalidArgumentException('Invalid mimetype');
    }
    return $this->_generate()->encode($mimetype, $quality);
  }
  /**
   * Save the generated Letter-Avatar as a file
   *
   * @param string $path
   * @param string $mimetype
   * @param int    $quality
   * @return bool
   */
  public function saveAs(string $path, string $mimetype = self::MIME_TYPE_PNG, $quality = 90): bool {
      if (empty($path)) {
        return false;
      }
    return \is_int(@file_put_contents($path, $this->encode($mimetype, $quality)));
  }
  /**
   * @return string
   */
  public function __toString(): string {
    return (string)$this->_generate()->encode('data-url');
  }
  /**
   * @param string $string
   * @return string
   */
  private function _stringToColor(string $string): string {
    // random color
    $rgb = \substr(\dechex(\crc32($string)), 0, 6);
    // make it darker
    $darker = 2;
    list($R16, $G16, $B16) = str_split($rgb, 2);
    $R = sprintf('%02X', floor(hexdec($R16) / $darker));
    $G = sprintf('%02X', floor(hexdec($G16) / $darker));
    $B = sprintf('%02X', floor(hexdec($B16) / $darker));
    return '#' . $R . $G . $B;
  }

}