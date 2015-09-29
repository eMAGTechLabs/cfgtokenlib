<?php

namespace ConfigToken\Tests\Options;


use ConfigToken\Tests\Options\Mocks\TestOptionsClass;

class OptionsTest extends \PHPUnit_Framework_TestCase
{
    public function testOptionKeys()
    {
        $options = new TestOptionsClass();
        $options->setOptionalKey('optional-temp');
        $this->assertTrue($options->isKeyOptional('optional-temp'));

        $options->setOptionalKeys(
            array(
                'optional-no-default' => null,
                'optional-default' => 'default-for-optional2',
            )
        );
        $this->assertFalse($options->hasKey('optional-temp'));

        $options->setOptionalKey('optional', 'default-for-optional');

        $this->assertTrue($options->isKeyOptional('optional'));
        $this->assertTrue($options->hasDefaultValue('optional-default'));
        $this->assertFalse($options->hasDefaultValue('optional-no-default'));
        $this->assertFalse($options->isKeyRequired('optional'));


        $options->setRequiredKey('required-temp');
        $this->assertFalse($options->isKeyOptional('required-temp'));
        $this->assertTrue($options->isKeyRequired('required-temp'));

        $options->setRequiredKeys(
            array(
                'required-no-default' => null,
                'required-default' => 'default-for-required2',
            )
        );
        $options->setRequiredKey('required', 'default-for-required');
        $this->assertFalse($options->hasKey('required-temp'));

        $this->assertFalse($options->isKeyOptional('required'));
        $this->assertTrue($options->isKeyRequired('required'));
        $this->assertTrue($options->isKeyRequired('required-default'));

        $options->setValue('optional', 'optional-value');
        $options->setValue('required', 'required-value');

        $this->assertEquals(
            array(
                'optional' => 'optional-value',
                'optional-no-default' => null,
                'optional-default' => 'default-for-optional2',
            ),
            $options->getOptionalKeys()
        );
        $this->assertEquals(
            array(
                'required' => 'required-value',
                'required-no-default' => null,
                'required-default' => 'default-for-required2',
            ),
            $options->getRequiredKeys()
        );
        $this->assertEquals(
            array(
                'optional' => false,
                'optional-no-default' => false,
                'optional-default' => false,
                'required' => true,
                'required-no-default' => true,
                'required-default' => true,
            ),
            $options->getKeys()
        );
        $this->assertEquals(array('unknown'), $options->getUnknownKeys(array('optional', 'unknown', 'required')));
        $this->assertEquals(
            array(
                'required-no-default',
                'required-default',
            ),
            $options->getRequiredKeysWithoutValues(false)
        );
        $this->assertEquals(
            array(
                'required-no-default',
            ),
            $options->getRequiredKeysWithoutValues(true)
        );
        $this->assertEquals(
            array(
                'optional' => 'optional-value',
                'required' => 'required-value',
            ),
            $options->getValues(false)
        );
        $this->assertEquals(
            array(
                'optional' => 'optional-value',
                'required' => 'required-value',
                'optional-default' => 'default-for-optional2',
                'required-default' => 'default-for-required2',
            ),
            $options->getValues(true)
        );

        $options->unsetValue('optional');

        $this->assertEquals(
            array(
                'required' => 'required-value',
            ),
            $options->getValues(false)
        );
        $this->assertEquals(
            array(
                'optional' => 'default-for-optional',
                'required' => 'required-value',
                'optional-default' => 'default-for-optional2',
                'required-default' => 'default-for-required2',
            ),
            $options->getValues(true)
        );

        $this->assertFalse($options->isValid(false));
        $this->assertFalse($options->isValid(true));

        $options->setDefaultValue('required-no-default', 'required-with-default');

        $this->assertFalse($options->isValid(false));
        $this->assertTrue($options->isValid(true));

        $options->unsetDefaultValue('required-no-default');

        $this->assertFalse($options->isValid(false));
        $this->assertFalse($options->isValid(true));

        $this->assertEquals('default-for-required2', $options->getValue('required-default', true));

        $options->setValues(
            array(
                'required' => 'required-value',
                'required-default' => 'required-value3',
                'required-no-default' => 'required-value2',
            )
        );

        $this->assertEquals('required-value3', $options->getValue('required-default', false));

        $this->assertTrue($options->isValid(false));
        $this->assertTrue($options->isValid(true));

        $options->setDefaultValues(
            array(
                'optional-default' => 'default-for-optional',
                'required-default' => 'default-for-required',
            )
        );

        $this->assertEquals(
            array(
                'optional-default' => 'default-for-optional',
                'required-default' => 'default-for-required',
            ),
            $options->getDefaultValues()
        );

        $this->assertEquals('default-for-optional', $options->getDefaultValue('optional-default'));

        $options->setValues(array());

        $this->assertEquals(
            array(
                'optional-default' => 'default-for-optional',
                'required-default' => 'default-for-required',
            ),
            $options->getValues(true)
        );

        $options->setValue('optional', 'optional-value');
        $options->setRequiredKey('optional', 'default-for-required-optional');

        $this->assertTrue($options->isKeyRequired('optional'));
        $this->assertTrue($options->hasDefaultValue('optional'));
        $this->assertEquals('optional-value', $options->getValue('optional'));

        $options->setOptionalKey('optional');

        $this->assertFalse($options->isKeyRequired('optional'));
        $this->assertFalse($options->hasDefaultValue('optional'));
        $this->assertEquals('optional-value', $options->getValue('optional'));

        $options->setValue('required', 'required-value');
    }

    /**
     * @expectedException \ConfigToken\Options\Exceptions\UnknownOptionException
     */
    public function testException1()
    {
        $options = new TestOptionsClass();
        $options->setRequiredKey('test');
        $options->unsetKey('test-not-found');
    }

    /**
     * @expectedException \ConfigToken\Options\Exceptions\UnknownOptionException
     */
    public function testException2()
    {
        $options = new TestOptionsClass();
        $options->setRequiredKey('test');
        $this->assertTrue($options->isKeyRequired('test'));
        $options->isKeyRequired('test-not-found');
    }

    /**
     * @expectedException \ConfigToken\Options\Exceptions\UnknownOptionException
     */
    public function testException3()
    {
        $options = new TestOptionsClass();
        $options->setRequiredKey('test');
        $options->isKeyOptional('test-not-found');
    }

    /**
     * @expectedException \ConfigToken\Options\Exceptions\UnknownOptionValueException
     */
    public function testException4()
    {
        $options = new TestOptionsClass();
        $options->setRequiredKey('test', 'default');
        $this->assertNotNull($options->unsetDefaultValue('test'));
        $options->unsetDefaultValue('test');
    }

    /**
     * @expectedException \ConfigToken\Options\Exceptions\UnknownOptionException
     */
    public function testException5()
    {
        $options = new TestOptionsClass();
        $options->setRequiredKey('test', 'default');
        $options->unsetDefaultValue('test-not-found');
    }

    /**
     * @expectedException \ConfigToken\Options\Exceptions\UnknownOptionException
     */
    public function testException6()
    {
        $options = new TestOptionsClass();
        $options->setRequiredKey('test', 'default');
        $options->setDefaultvalues(array('test-not-found' => 'default'));
    }

    /**
     * @expectedException \ConfigToken\Options\Exceptions\UnknownOptionException
     */
    public function testException7()
    {
        $options = new TestOptionsClass();
        $options->setRequiredKey('test', 'default');
        $options->hasDefaultValue('test-not-found');
    }

    /**
     * @expectedException \ConfigToken\Options\Exceptions\UnknownOptionException
     */
    public function testException8()
    {
        $options = new TestOptionsClass();
        $options->setRequiredKey('test', 'default');
        $options->getDefaultValue('test-not-found');
    }

    /**
     * @expectedException \ConfigToken\Options\Exceptions\UnknownOptionValueException
     */
    public function testException9()
    {
        $options = new TestOptionsClass();
        $options->setRequiredKey('test');
        $options->getDefaultValue('test');
    }

    /**
     * @expectedException \ConfigToken\Options\Exceptions\UnknownOptionException
     */
    public function testException10()
    {
        $options = new TestOptionsClass();
        $options->setRequiredKey('test');
        $this->assertNotNull($options->setDefaultValue('test', 'default'));
        $options->setDefaultValue('test-not-found', 'default');
    }

    /**
     * @expectedException \ConfigToken\Options\Exceptions\OptionValueException
     */
    public function testException11()
    {
        $options = new TestOptionsClass();
        $options->setRequiredKey('invalid');
        $options->setDefaultValue('invalid', 'default');
    }

    /**
     * @expectedException \ConfigToken\Options\Exceptions\OptionValueException
     */
    public function testException12()
    {
        $options = new TestOptionsClass();
        $options->setRequiredKey('invalid');
        $options->setValues(array('invalid' => 'default'));
    }

    /**
     * @expectedException \ConfigToken\Options\Exceptions\UnknownOptionException
     */
    public function testException13()
    {
        $options = new TestOptionsClass();
        $options->setRequiredKey('invalid');
        $options->setValues(array('not-found' => 'default'));
    }

    /**
     * @expectedException \ConfigToken\Options\Exceptions\UnknownOptionException
     */
    public function testException14()
    {
        $options = new TestOptionsClass();
        $options->setRequiredKey('invalid');
        $this->assertFalse($options->hasValue('invalid'));
        $options->hasValue('not-found');
    }

    /**
     * @expectedException \ConfigToken\Options\Exceptions\UnknownOptionException
     */
    public function testException15()
    {
        $options = new TestOptionsClass();
        $options->setRequiredKey('invalid');
        $options->getValue('not-found');
    }

    /**
     * @expectedException \ConfigToken\Options\Exceptions\UnknownOptionValueException
     */
    public function testException16()
    {
        $options = new TestOptionsClass();
        $options->setRequiredKey('required', 'default');
        $this->assertEquals('default', $options->getValue('required', true));
        $options->getValue('required', false);
    }

    /**
     * @expectedException \ConfigToken\Options\Exceptions\UnknownOptionValueException
     */
    public function testException17()
    {
        $options = new TestOptionsClass();
        $options->setRequiredKey('required');
        $options->getValue('required', true);
    }

    /**
     * @expectedException \ConfigToken\Options\Exceptions\UnknownOptionException
     */
    public function testException18()
    {
        $options = new TestOptionsClass();
        $options->setRequiredKey('required');
        $options->isValidValue('not-found', 'value');
    }

    /**
     * @expectedException \ConfigToken\Options\Exceptions\UnknownOptionException
     */
    public function testException19()
    {
        $options = new TestOptionsClass();
        $options->setRequiredKey('required');
        $options->unsetValue('not-found');
    }

    /**
     * @expectedException \ConfigToken\Options\Exceptions\UnknownOptionValueException
     */
    public function testException20()
    {
        $options = new TestOptionsClass();
        $options->setRequiredKey('required');
        $options->unsetValue('required');
    }
}