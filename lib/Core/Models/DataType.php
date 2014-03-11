<?php
/**
 * Model field data type
 * @property-read int $type Type (see type constants)
 * @property-read boolean $null Whether or not type is nullable
 * @property-read boolean $notNull Opposite of null
 * @property-read mixed $default Default value
 * @property-read int $length Length (strings only)
 * @property-read int $size Size: BIG, SMALL, TINY or 0 (integers only)
 * @property-read boolean $signed Signed (integers only)
 * @property-read boolean $unsigned Opposite of signed (integers only)
 * @property-read boolean $autoIncrement Auto increment (integers only)
 * @package Core\Models
 */
class DataType {
  /** @var int Type: Integer */
  const INTEGER = 1;
  /** @var int Type: String (length <= 255) */
  const STRING = 2;
  /** @var int Type: Text */
  const TEXT = 3;
  /** @var int Type: Boolean*/
  const BOOLEAN = 4;
  /** @var int Type: Float */
  const FLOAT = 5;
  /** @var int Type: Date */
  const DATE = 6;
  /** @var int Type: Date/time */
  const DATETIME = 7;
  /** @var int Type: Binary object */
  const BINARY = 8;

  /** @var int Flag: Unsigned (integers only) */
  const UNSIGNED = 0x02;
  /** @var int Flag: Auto increment (integers only) */
  const AUTO_INCREMENT = 0x04;
  /** @var int Flag: Tiny integer (8 bit) (integers only) */
  const TINY = 0x10;
  /** @var int Flag: Small integer (16 bit) (integers only) */
  const SMALL = 0x20;
  /** @var int Flag: Big integer (64 bit) (integers only) */
  const BIG = 0x30;
  
  /** @var int Type */
  private $type;
  /** @var boolean Null */
  private $null = false;
  /** @var int String length */
  private $length = null;
  /** @var boolean Signed */
  private $signed = true;
  /** @var boolean Auto increment */
  private $autoIncrement = false;
  /** @var mixed Default value */
  private $default = null;
  /** @var int Integer size */
  private $size = null;
  
  /**
   * Constructor
   * @param int $type Type
   * @param int $flags Flags
   * @param int|null $length String length
   * @param mixed Default value
   */
  private function __construct($type, $null = false, $default = null, $flags = 0, $length = null) {
    if ($type < 0 or $type > 8)
      throw new InvalidDataTypeException(tr('%1 is not a valid type'), $type);
    $this->type = $type;
    $this->length = $length;
    $this->default = $default;
    $this->null = $null;
    if ($type == self::INTEGER) {
      $this->signed = ($flags & self::UNSIGNED) == 0;
      $this->autoIncrement = ($flags & self::AUTO_INCREMENT) != 0;
      $this->size = $flags & 0x30;
    }
    else if ($flags != 0) {
      throw new InvalidDataTypeException(tr('Using integer flags for non-integer type'));
    }
  }
  
  /**
   * Get property value
   * @param string $property Property name
   * @return mixed Property value
   */
  public function __get($property) {
    switch ($property) {
      case 'type':
      case 'null':
      case 'default':
        return $this->$property;
      case 'notNull':
        return !$this->null;
    }
    if ($this->type == self::STRING) {
      switch ($property) {
        case 'length':
          return $this->$property;
      }
    }
    if ($this->type == self::INTEGER) {
      switch ($property) {
        case 'size':
        case 'signed':
        case 'autoIncrement':
          return $this->$property;
        case 'unsigned':
          return !$this->signed;
      }
    }
  }

  public function __isset($property) {
    return $this->$property !== null;
  }

  public function __toString() {
    switch ($this->type) {
    }
  }

  /** @return Whether or not the type is integer */
  public function isInteger() {
    return $this->type == self::INTEGER;
  }

  /** @return Whether or not the type is string */
  public function isString() {
    return $this->type == self::STRING;
  }

  /** @return Whether or not the type is text */
  public function isText() {
    return $this->type == self::TEXT;
  }

  /** @return Whether or not the type is boolean */
  public function isBoolean() {
    return $this->type == self::BOOLEAN;
  }

  /** @return Whether or not the type is float */
  public function isFloat() {
    return $this->type == self::FLOAT;
  }

  /** @return Whether or not the type is date */
  public function isDate() {
    return $this->type == self::DATE;
  }

  /** @return Whether or not the type is date/time */
  public function isDateTime() {
    return $this->type == self::DATETIME;
  }

  /** @return Whether or not the type is binary */
  public function isBinary() {
    return $this->type == self::BINARY;
  }

  public function createValidationRules(ValidatorField $validator) {
    $validator = $validator->ruleDataType;
    if (!$this->null && $this->type != self::INTEGER && !$this->autoIncrement)
      $validator->null = false;
    switch ($this->type) {
      case self::INTEGER:
        $validator->integer = true;
        if ($this->signed) {
          switch ($this->size) {
            case self::BIG:
              return;
            case self::SMALL:
              $validator->minValue = -32768;
              $validator->maxValue = 32767;
              return;
            case self::TINY:
              $validator->minValue = -128;
              $validator->maxValue = 127;
              return;
            default:
              $validator->minValue = -2147483648;
              $validator->maxValue = 2147483647;
              return;
          }
        }
        else {
          $validator->minValue = 0;
          switch ($this->size) {
            case self::BIG:
              return;
            case self::SMALL:
              $valudator->maxValue = 65535;
              return;
            case self::TINY:
              $validator->maxValue = 255;
              return;
            default:
              $validator->maxValue = 4294967295;
              return;
          }
        }
        return;
      case self::STRING:
        $validator->maxLength = $this->length;
        return;
      case self::BOOLEAN:
        $validator->boolean = true;
        return;
      case self::FLOAT:
        $validator->float = true;
        return;
      case self::DATE:
      case self::DATETIME:
        $validator->date = true;
        return;
      case self::TEXT:
      case self::BINARY:
        return;
    }
  }

  /**
   * Check if value is of this type
   * @param mixed $value Value to test
   * @return boolean True if it is, false otherwise
   */
  public function isValid($value) {
    if ($this->null and $value == null)
      return true;
    switch ($this->type) {
      case self::INTEGER:
        if (!is_int($value))
          return false;
        if ($this->signed) {
          switch ($this->size) {
            case self::BIG:
              return true;
            case self::SMALL:
              return $value >= -32768 and $value <= 32767;
            case self::TINY:
              return $value >= -128 and $value <= 127;
            default:
              return $value >= -2147483648 and $value <= 2147483647;
          }
        }
        else {
          if ($value < 0)
            return false;
          switch ($this->size) {
            case self::BIG:
              return true;
            case self::SMALL:
              return $value <= 65535;
            case self::TINY:
              return $value <= 255;
            default:
              return $value <= 4294967295;
          }
        }
      case self::STRING:
        return is_string($value) and strlen($value) <= $this->length;
      case self::BOOLEAN:
        return is_bool($value);
      case self::FLOAT:
        return is_float($value);
      case self::DATE:
      case self::DATETIME:
        return is_int($value);
      case self::TEXT:
      case self::BINARY:
        return is_string($value);
    }
    return false;
  }
  
  public function convert($value) {
    //if ($this->null and $value == null)
    if ($value == null)
      return null;
    switch ($this->type) {
      case self::INTEGER:
        return intval($value);
      case self::STRING:
        return strval($value);
      case self::BOOLEAN:
        return (bool) $value;
      case self::FLOAT:
        return floatval($value);
      case self::DATE:
      case self::DATETIME:
        if (is_int($value))
          return $value;
        return strtotime($value);
      case self::TEXT:
      case self::BINARY:
        return strval($value);
    }
    return null;
  }
  
  /**
   * Create integer type
   * @param int $flags Combination of: UNSIGNED, AUTO_INCREMENT, BIG, SMALL, TINY
   * @param boolean $null Whether or not type is nullable
   * @param int|null $default Default value
   * @return DataType Type object
   */
  public static function integer($flags = 0, $null = false, $default = null) {
    return new self(self::INTEGER, $null, $default, $flags);
  }
  
  /**
   * Create string type
   * @param int $length String maximum length (0 to 255)
   * @param boolean $null Whether or not type is nullable
   * @param string $default Default value
   * @return DataType Type object
   */
  public static function string($length = 255, $null = false, $default = null) {
    return new self(self::STRING, $null, $default, 0, $length);
  }
  
  /**
   * Create text type
   * @param boolean $null Whether or not type is nullable
   * @param string $default Default value
   * @return DataType Type object
   */
  public static function text($null = false, $default = null) {
    return new self(self::TEXT, $null, $default);
  }
  
  /**
   * Create boolean type
   * @param boolean $null whether or not type is nullable
   * @param boolean $default default value
   * @return datatype type object
   */
  public static function boolean($null = false, $default = null) {
    return new self(self::BOOLEAN, $null, $default);
  }
  
  /**
   * Create float type
   * @param boolean $null whether or not type is nullable
   * @param float $default default value
   * @return datatype type object
   */
  public static function float($null = false, $default = null) {
    return new self(self::FLOAT, $null, $default);
  }

  /**
   * Create date type
   * @param boolean $null whether or not type is nullable
   * @param int $default default value (unix timestamp)
   * @return datatype type object
   */
  public static function date($null = false, $default = null) {
    return new self(self::DATE, $null, $default);
  }
  
  /**
   * Create date/time type
   * @param boolean $null whether or not type is nullable
   * @param int $default default value (unix timestamp)
   * @return datatype type object
   */
  public static function dateTime($null = false, $default = null) {
    return new self(self::DATETIME, $null, $default);
  }
  
  /**
   * Create binary object type
   * @param boolean $null whether or not type is nullable
   * @param string $default default value
   * @return datatype type object
   */
  public static function binary($null = false, $default = null) {
    return new self(self::BINARY, $null, $default);
  }
  
  public static function detectType($value) {
    if (is_bool($value))
      return self::boolean();
    if (is_int($value))
      return self::integer();
    if (is_float($value))
      return self::float();
    return self::text();
  }
  
  public static function fromPlaceholder($placeholder) {
    $placeholder = strtolower($placeholder);
    switch ($placeholder) {
      case 'i':
      case 'int':
      case 'integer':
        return self::integer(self::BIG);
      case 'f':
      case 'float':
        return self::float();
      case 's':
      case 'str':
      case 'string':
        return self::string(255);
      case 't':
      case 'text':
        return self::text();
      case 'b':
      case 'bool':
      case 'boolean':
        return self::boolean();
      case 'date':
        return self::date();
      case 'd':
      case 'datetime':
        return self::dateTime();
      case 'n':
      case 'bin':
      case 'binary':
        return self::binary();
    }
  }
}
