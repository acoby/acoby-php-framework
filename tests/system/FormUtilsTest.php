<?php
declare(strict_types=1);

namespace acoby\system;

use acoby\BaseTestCase;
use acoby\forms\InputField;
use stdClass;

class FormUtilsTest extends BaseTestCase {
  
  public function testForms1() {
    $element = FormUtils::getInstance()->createInputField("tab", "name", "label", "placeholder", "currentValue", true, [$this,"validateElement"], false, 0, 100, "pattern");
    
    $this->assertEquals("tab", $element->tab);
    $this->assertEquals("name", $element->id);
    $this->assertEquals("name", $element->name);
    $this->assertEquals("label", $element->label);
    $this->assertEquals("placeholder", $element->placeholder);
    $this->assertEquals("currentValue", $element->currentValue);
    $this->assertEquals("pattern", $element->pattern);
    $this->assertTrue($element->mandatory);
    $this->assertFalse($element->readonly);
    $this->assertEquals(0, $element->minlength);
    $this->assertEquals(100, $element->maxlength);
    $this->assertCount(2, $element->validator);
    
    $element->newValue = "123";
    $this->assertTrue($element->isChanged());
    $object = new stdClass();
    $this->assertTrue($element->validate($object));
  }

  public function testForms2() {
    $element = FormUtils::getInstance()->createEMailField("tab", "name", "label", "placeholder", "currentValue", true, [$this,"validateElement"], false, 0, 100, "pattern");
    
    $this->assertEquals("tab", $element->tab);
    $this->assertEquals("name", $element->id);
    $this->assertEquals("name", $element->name);
    $this->assertEquals("label", $element->label);
    $this->assertEquals("placeholder", $element->placeholder);
    $this->assertEquals("currentValue", $element->currentValue);
    $this->assertEquals("pattern", $element->pattern);
    $this->assertTrue($element->mandatory);
    $this->assertFalse($element->readonly);
    $this->assertEquals(0, $element->minlength);
    $this->assertEquals(100, $element->maxlength);
    $this->assertCount(2, $element->validator);

    $element->newValue = "123";
    $this->assertTrue($element->isChanged());
    
    $object = new stdClass();
    $this->assertFalse($element->validate($object));

    $element->newValue = "nobody@example.com";
    $this->assertTrue($element->validate($object));
  }

  public function testForms3() {
    $element = FormUtils::getInstance()->createURLField("tab", "name", "label", "placeholder", "currentValue", true, [$this,"validateElement"], false, 0, 100, "pattern");
    
    $this->assertEquals("tab", $element->tab);
    $this->assertEquals("name", $element->id);
    $this->assertEquals("name", $element->name);
    $this->assertEquals("label", $element->label);
    $this->assertEquals("placeholder", $element->placeholder);
    $this->assertEquals("currentValue", $element->currentValue);
    $this->assertEquals("pattern", $element->pattern);
    $this->assertTrue($element->mandatory);
    $this->assertFalse($element->readonly);
    $this->assertEquals(0, $element->minlength);
    $this->assertEquals(100, $element->maxlength);
    $this->assertCount(2, $element->validator);
    
    $element->newValue = "123";
    $this->assertTrue($element->isChanged());
    
    $object = new stdClass();
    $this->assertFalse($element->validate($object));
    
    $element->newValue = "http://example.com/";
    $this->assertTrue($element->validate($object));
  }

  public function testForms4() {
    $element = FormUtils::getInstance()->createPasswordField("tab", "name", "label", "placeholder", "currentValue", true, [$this,"validateElement"], false, 5, 100, "pattern");
    
    $this->assertEquals("tab", $element->tab);
    $this->assertEquals("name", $element->id);
    $this->assertEquals("name", $element->name);
    $this->assertEquals("label", $element->label);
    $this->assertEquals("placeholder", $element->placeholder);
    $this->assertEquals("currentValue", $element->currentValue);
    $this->assertEquals("pattern", $element->pattern);
    $this->assertTrue($element->mandatory);
    $this->assertFalse($element->readonly);
    $this->assertEquals(5, $element->minlength);
    $this->assertEquals(100, $element->maxlength);
    $this->assertCount(2, $element->validator);

    $element->newValue = "123";
    $this->assertTrue($element->isChanged());
    
    $object = new stdClass();
    $this->assertFalse($element->validate($object));
    
    $element->newValue = "1234567";
    $this->assertTrue($element->validate($object));
  }
  

  public function testForms5() {
    $element = FormUtils::getInstance()->createCheckbox("tab", "name", "label", true, [$this,"validateElement"]);
    
    $this->assertEquals("tab", $element->tab);
    $this->assertEquals("name", $element->id);
    $this->assertEquals("name", $element->name);
    $this->assertEquals("label", $element->label);
    $this->assertTrue($element->currentValue);
    $this->assertFalse($element->readonly);
    $this->assertCount(2, $element->validator);

    $element->newValue = "123";
    $this->assertTrue($element->isChanged());
    
    $object = new stdClass();
    $this->assertTrue($element->validate($object));
    $this->assertEquals("false",$element->newValue);
    
    $element->newValue = "on";
    $this->assertTrue($element->validate($object));
    $this->assertEquals("true",$element->newValue);
    
    $element->newValue = "xxxx";
    $this->assertTrue($element->validate($object));
    $this->assertEquals("false",$element->newValue);
  }
  
  public function testForms6() {
    $element = FormUtils::getInstance()->createMultilineInputField("tab", "name", "label", "placeholder", "currentValue", true, [$this,"validateElement"], 5);
    
    $this->assertEquals("tab", $element->tab);
    $this->assertEquals("name", $element->id);
    $this->assertEquals("name", $element->name);
    $this->assertEquals("label", $element->label);
    $this->assertEquals("placeholder", $element->placeholder);
    $this->assertEquals("currentValue", $element->currentValue);
    $this->assertTrue($element->mandatory);
    $this->assertFalse($element->readonly);
    $this->assertCount(2, $element->validator);
  }
  public function testForms7() {
    $element = FormUtils::getInstance()->createNumberField("tab", "name", "label", "placeholder", 100, true, [$this,"validateElement"], 1, 200);
    
    $this->assertEquals("tab", $element->tab);
    $this->assertEquals("name", $element->id);
    $this->assertEquals("name", $element->name);
    $this->assertEquals("label", $element->label);
    $this->assertEquals("placeholder", $element->placeholder);
    $this->assertEquals(100, $element->currentValue);
    $this->assertTrue($element->mandatory);
    $this->assertFalse($element->readonly);
    $this->assertEquals(1, $element->minValue);
    $this->assertEquals(200, $element->maxValue);
    $this->assertEquals(1, $element->minlength);
    $this->assertEquals(3, $element->maxlength);
    $this->assertCount(2, $element->validator);
    
    $element->newValue = "123";
    $this->assertTrue($element->isChanged());
    
    $object = new stdClass();
    $this->assertTrue($element->validate($object));
    
    $element->newValue = "5";
    $this->assertTrue($element->validate($object));
    
    $element->newValue = "0";
    $this->assertFalse($element->validate($object));
    
    $element->newValue = "201";
    $this->assertFalse($element->validate($object));
  
    $element->newValue = null;
    $this->assertFalse($element->validate($object));
  }
  public function testForms8() {
    $element = FormUtils::getInstance()->createPhoneField("tab", "name", "label", "placeholder", "currentValue", true, [$this,"validateElement"], false, 0, 100, "pattern");
    
    $this->assertEquals("tab", $element->tab);
    $this->assertEquals("name", $element->id);
    $this->assertEquals("name", $element->name);
    $this->assertEquals("label", $element->label);
    $this->assertEquals("placeholder", $element->placeholder);
    $this->assertEquals("currentValue", $element->currentValue);
    $this->assertEquals("pattern", $element->pattern);
    $this->assertTrue($element->mandatory);
    $this->assertFalse($element->readonly);
    $this->assertEquals(0, $element->minlength);
    $this->assertEquals(100, $element->maxlength);
    $this->assertCount(2, $element->validator);
  }
  public function testForms9() {
    $currentValue = ["on","off"];
    $element = FormUtils::getInstance()->createMultiSelectField("tab", "name", "label", "placeholder", $currentValue, true, [$this,"validateElement"], [["value"=>"on","name"=>"On"],["value"=>"off","name"=>"Off"],["value"=>"maybe","name"=>"Maybe"]], false);
    
    $this->assertEquals("tab", $element->tab);
    $this->assertEquals("name", $element->id);
    $this->assertEquals("name", $element->name);
    $this->assertEquals("label", $element->label);
    $this->assertEquals("placeholder", $element->placeholder);
    $this->assertEquals("on,off", $element->currentValue);
    $this->assertTrue($element->mandatory);
    $this->assertFalse($element->readonly);
    $this->assertCount(2, $element->validator);
  }
  public function testFormsA() {
    $element = FormUtils::getInstance()->createSelectField("tab", "name", "label", "placeholder", "currentValue", true, [$this,"validateElement"], [["value"=>"on","name"=>"On"],["value"=>"off","name"=>"Off"]]);
    
    $this->assertEquals("tab", $element->tab);
    $this->assertEquals("name", $element->id);
    $this->assertEquals("name", $element->name);
    $this->assertEquals("label", $element->label);
    $this->assertEquals("placeholder", $element->placeholder);
    $this->assertEquals("currentValue", $element->currentValue);
    $this->assertTrue($element->mandatory);
    $this->assertFalse($element->readonly);
    $this->assertCount(2, $element->validator);
  }
  public function testFormsB() {
    $element = FormUtils::getInstance()->createSelect2Field("tab", "name", "label", "placeholder", "currentValue", true, [$this,"validateElement"], "url");
    
    $this->assertEquals("tab", $element->tab);
    $this->assertEquals("name", $element->id);
    $this->assertEquals("name", $element->name);
    $this->assertEquals("label", $element->label);
    $this->assertEquals("placeholder", $element->placeholder);
    $this->assertEquals("currentValue", $element->currentValue);
    $this->assertTrue($element->mandatory);
    $this->assertFalse($element->readonly);
    $this->assertCount(2, $element->validator);
  }
  
  public function validateElement(object $object, InputField $element) :bool {
    return true;
  }
}