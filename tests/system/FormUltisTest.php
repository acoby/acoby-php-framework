<?php
declare(strict_types=1);

namespace acoby\system;

use acoby\BaseTestCase;

class FormUtilsTest extends BaseTestCase {
  public function testForms1() {
    $element = FormUtils::getInstance()->createInputField("tab", "name", "label", "placeholder", "currentValue", true, ["x"], false, 0, 100, "pattern");
    
    $this->assertEquals("tab", $element->tab);
    $this->assertEquals("name", $element->id);
    $this->assertEquals("name", $element->name);
    $this->assertEquals("label", $element->label);
    $this->assertEquals("placeholder", $element->placeholder);
    $this->assertEquals("currentValue", $element->currentValue);
    $this->assertEquals("pattern", $element->pattern);
    $this->assertEquals(true, $element->mandatory);
    $this->assertEquals(false, $element->readonly);
    $this->assertEquals(0, $element->minlength);
    $this->assertEquals(100, $element->maxlength);
    $this->assertEquals(1, count($element->validator));
    $this->assertTrue($element->isChanged("new"));
    $this->assertTrue($element->validate("123"));
  }

  public function testForms2() {
    $element = FormUtils::getInstance()->createEMailField("tab", "name", "label", "placeholder", "currentValue", true, ["x"], false, 0, 100, "pattern");
    
    $this->assertEquals("tab", $element->tab);
    $this->assertEquals("name", $element->id);
    $this->assertEquals("name", $element->name);
    $this->assertEquals("label", $element->label);
    $this->assertEquals("placeholder", $element->placeholder);
    $this->assertEquals("currentValue", $element->currentValue);
    $this->assertEquals("pattern", $element->pattern);
    $this->assertEquals(true, $element->mandatory);
    $this->assertEquals(false, $element->readonly);
    $this->assertEquals(0, $element->minlength);
    $this->assertEquals(100, $element->maxlength);
    $this->assertEquals(1, count($element->validator));

    $this->assertTrue($element->isChanged("new"));
    $this->assertFalse($element->validate($element->currentValue));
    $this->assertTrue($element->validate("nobody@example.com"));
  }

  public function testForms3() {
    $element = FormUtils::getInstance()->createURLField("tab", "name", "label", "placeholder", "currentValue", true, ["x"], false, 0, 100, "pattern");
    
    $this->assertEquals("tab", $element->tab);
    $this->assertEquals("name", $element->id);
    $this->assertEquals("name", $element->name);
    $this->assertEquals("label", $element->label);
    $this->assertEquals("placeholder", $element->placeholder);
    $this->assertEquals("currentValue", $element->currentValue);
    $this->assertEquals("pattern", $element->pattern);
    $this->assertEquals(true, $element->mandatory);
    $this->assertEquals(false, $element->readonly);
    $this->assertEquals(0, $element->minlength);
    $this->assertEquals(100, $element->maxlength);
    $this->assertEquals(1, count($element->validator));

    $this->assertTrue($element->isChanged("new"));
    $this->assertFalse($element->validate($element->currentValue));
    $this->assertTrue($element->validate("http://example.com/"));
  }

  public function testForms4() {
    $element = FormUtils::getInstance()->createPasswordField("tab", "name", "label", "placeholder", "currentValue", true, ["x"], false, 5, 100, "pattern");
    
    $this->assertEquals("tab", $element->tab);
    $this->assertEquals("name", $element->id);
    $this->assertEquals("name", $element->name);
    $this->assertEquals("label", $element->label);
    $this->assertEquals("placeholder", $element->placeholder);
    $this->assertEquals("currentValue", $element->currentValue);
    $this->assertEquals("pattern", $element->pattern);
    $this->assertEquals(true, $element->mandatory);
    $this->assertEquals(false, $element->readonly);
    $this->assertEquals(5, $element->minlength);
    $this->assertEquals(100, $element->maxlength);
    $this->assertEquals(1, count($element->validator));
  
    $this->assertTrue($element->isChanged("new"));
    $this->assertFalse($element->validate("new"));
    $this->assertTrue($element->validate("1234567"));
  }
  

  public function testForms5() {
    $element = FormUtils::getInstance()->createCheckbox("tab", "name", "label", true, ["x"], false);
    
    $this->assertEquals("tab", $element->tab);
    $this->assertEquals("name", $element->id);
    $this->assertEquals("name", $element->name);
    $this->assertEquals("label", $element->label);
    $this->assertEquals(true, $element->currentValue);
    $this->assertEquals(false, $element->readonly);
    $this->assertEquals(1, count($element->validator));

    $this->assertTrue($element->isChanged("new"));
    $this->assertTrue($element->validate($element->currentValue));
    $this->assertTrue($element->validate("on"));
    $this->assertTrue($element->validate("xxxx"));
  }
  
  public function testForms6() {
    $element = FormUtils::getInstance()->createMultilineInputField("tab", "name", "label", "placeholder", "currentValue", true, ["x"], 5, false);
    
    $this->assertEquals("tab", $element->tab);
    $this->assertEquals("name", $element->id);
    $this->assertEquals("name", $element->name);
    $this->assertEquals("label", $element->label);
    $this->assertEquals("placeholder", $element->placeholder);
    $this->assertEquals("currentValue", $element->currentValue);
    $this->assertEquals(true, $element->mandatory);
    $this->assertEquals(false, $element->readonly);
    $this->assertEquals(1, count($element->validator));
  }
  public function testForms7() {
    $element = FormUtils::getInstance()->createNumberField("tab", "name", "label", "placeholder", 100, true, ["x"], 1, 200, false);
    
    $this->assertEquals("tab", $element->tab);
    $this->assertEquals("name", $element->id);
    $this->assertEquals("name", $element->name);
    $this->assertEquals("label", $element->label);
    $this->assertEquals("placeholder", $element->placeholder);
    $this->assertEquals(100, $element->currentValue);
    $this->assertEquals(true, $element->mandatory);
    $this->assertEquals(false, $element->readonly);
    $this->assertEquals(1, $element->minValue);
    $this->assertEquals(200, $element->maxValue);
    $this->assertEquals(1, $element->minlength);
    $this->assertEquals(3, $element->maxlength);
    $this->assertEquals(1, count($element->validator));
    
    $this->assertTrue($element->validate($element->currentValue));
    $this->assertFalse($element->validate(null));
    $this->assertFalse($element->validate(0));
    $this->assertFalse($element->validate(201));
  }
  public function testForms8() {
    $element = FormUtils::getInstance()->createPhoneField("tab", "name", "label", "placeholder", "currentValue", true, ["x"], false, 0, 100, "pattern");
    
    $this->assertEquals("tab", $element->tab);
    $this->assertEquals("name", $element->id);
    $this->assertEquals("name", $element->name);
    $this->assertEquals("label", $element->label);
    $this->assertEquals("placeholder", $element->placeholder);
    $this->assertEquals("currentValue", $element->currentValue);
    $this->assertEquals("pattern", $element->pattern);
    $this->assertEquals(true, $element->mandatory);
    $this->assertEquals(false, $element->readonly);
    $this->assertEquals(0, $element->minlength);
    $this->assertEquals(100, $element->maxlength);
    $this->assertEquals(1, count($element->validator));
  }
  public function testForms9() {
    $currentValue = ["on","off"];
    $element = FormUtils::getInstance()->createMultiSelectField("tab", "name", "label", "placeholder", $currentValue, true, ["x"], [["value"=>"on","name"=>"On"],["value"=>"off","name"=>"Off"],["value"=>"maybe","name"=>"Maybe"]], false);
    
    $this->assertEquals("tab", $element->tab);
    $this->assertEquals("name", $element->id);
    $this->assertEquals("name", $element->name);
    $this->assertEquals("label", $element->label);
    $this->assertEquals("placeholder", $element->placeholder);
    $this->assertEquals("on,off", $element->currentValue);
    $this->assertEquals(true, $element->mandatory);
    $this->assertEquals(false, $element->readonly);
    $this->assertEquals(1, count($element->validator));
  }
  public function testFormsA() {
    $element = FormUtils::getInstance()->createSelectField("tab", "name", "label", "placeholder", "currentValue", true, ["x"], [["value"=>"on","name"=>"On"],["value"=>"off","name"=>"Off"]], false);
    
    $this->assertEquals("tab", $element->tab);
    $this->assertEquals("name", $element->id);
    $this->assertEquals("name", $element->name);
    $this->assertEquals("label", $element->label);
    $this->assertEquals("placeholder", $element->placeholder);
    $this->assertEquals("currentValue", $element->currentValue);
    $this->assertEquals(true, $element->mandatory);
    $this->assertEquals(false, $element->readonly);
    $this->assertEquals(1, count($element->validator));
  }
  public function testFormsB() {
    $element = FormUtils::getInstance()->createSelect2Field("tab", "name", "label", "placeholder", "currentValue", true, ["x"], "url", false);
    
    $this->assertEquals("tab", $element->tab);
    $this->assertEquals("name", $element->id);
    $this->assertEquals("name", $element->name);
    $this->assertEquals("label", $element->label);
    $this->assertEquals("placeholder", $element->placeholder);
    $this->assertEquals("currentValue", $element->currentValue);
    $this->assertEquals(true, $element->mandatory);
    $this->assertEquals(false, $element->readonly);
    $this->assertEquals(1, count($element->validator));
  }
}