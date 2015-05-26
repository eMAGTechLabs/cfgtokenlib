<?php

namespace ConfigToken\Tests\TreeCompiler;


use ConfigToken\TreeCompiler;
use ConfigToken\TreeCompiler\Xref;

class TreeCompilerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \ConfigToken\TreeCompiler\Exception\TokenResolverDefinitionException
     * @expectedExceptionMessage Token resolver definition at index 0 for Xref key "dep5" is missing the "type" type identifier key.
     * {"options":{"token-prefix":"{{","token-suffix":"}}"},"values":{"here":"resolved"}}
     */
    public function testTokenResolverDefinitionValidator1()
    {
        $xrefDep5 = new Xref('file', 'dep5.json');
        $xrefDep5->setData(
            array(
                'key_with_registered_token' => 'token {{here}}',
            )
        )->setResolved(true);

        $xrefDep4 = new Xref('file', 'dep4.json');
        $xrefDep4->setData(
            array(
                'include' => array(
                    'xref' => array(
                        'dep5' => array(
                            'type' => 'file',
                            'src' => 'dep5.json',
                            'resolve' => array(
                                array(
                                    'options' => array(
                                        'token-prefix' => '{{',
                                        'token-suffix' => '}}',
                                    ),
                                    'values' => array(
                                        'here' => 'resolved',
                                    ),
                                ),
                            ),
                        ),
                    ),
                    'main' => array(
                        'dep5',
                    ),
                ),
                'add' => array(
                    'key_from_dep4' => 'value from dep4'
                ),
                'remove' => array(
                    'key_to_remove' => '',
                ),
            )
        )->setResolved(true);

        $treeCompiler = new TreeCompiler();
        $treeCompiler->getXrefs()->add($xrefDep4);
        $treeCompiler->getXrefs()->add($xrefDep5);

        $treeCompiler->compileXref($xrefDep4);
    }

    /**
     * @expectedException \ConfigToken\TreeCompiler\Exception\TokenResolverDefinitionException
     * @expectedExceptionMessage Token resolver definition at index 0 for Xref key "dep5" must be an associative array.
     */
    public function testTokenResolverDefinitionValidator2()
    {
        $xrefDep5 = new Xref('file', 'dep5.json');
        $xrefDep5->setData(
            array(
                'key_with_registered_token' => 'token {{here}}',
            )
        )->setResolved(true);

        $xrefDep4 = new Xref('file', 'dep4.json');
        $xrefDep4->setData(
            array(
                'include' => array(
                    'xref' => array(
                        'dep5' => array(
                            'type' => 'file',
                            'src' => 'dep5.json',
                            'resolve' => array(
                                1
                            ),
                        ),
                    ),
                    'main' => array(
                        'dep5',
                    ),
                ),
                'add' => array(
                    'key_from_dep4' => 'value from dep4'
                ),
                'remove' => array(
                    'key_to_remove' => '',
                ),
            )
        )->setResolved(true);

        $treeCompiler = new TreeCompiler();
        $treeCompiler->getXrefs()->add($xrefDep4);
        $treeCompiler->getXrefs()->add($xrefDep5);

        $treeCompiler->compileXref($xrefDep4);
    }

    public function testTreeCompiler()
    {
        $xrefDep5 = new Xref('file', 'dep5.json');
        $xrefDep5->setData(
            array(
                'key_from_dep5' => 'value from dep5',
                'key_with_registered_token' => 'token {{here}}',
                'key_to_remove' => 'value from dep5',
            )
        )->setResolved(true);

        $xrefDep4 = new Xref('file', 'dep4.json');
        $xrefDep4->setData(
            array(
                'include' => array(
                    'xref' => array(
                        'dep5' => array(
                            'type' => 'file',
                            'src' => 'dep5.json',
                            'resolve' => array(
                                array(
                                    'type' => 'registered',
                                    'options' => array(
                                        'token-prefix' => '{{',
                                        'token-suffix' => '}}',
                                    ),
                                    'values' => array(
                                        'here' => 'resolved',
                                    ),
                                ),
                            ),
                        ),
                    ),
                    'main' => array(
                        'dep5',
                    ),
                ),
                'add' => array(
                    'key_from_dep4' => 'value from dep4'
                ),
                'remove' => array(
                    'key_to_remove' => '',
                ),
            )
        )->setResolved(true);

        $xrefDep3 = new Xref('file', 'dep3.json');
        $xrefDep3->setData(
            array(
                'include' => array(
                    'xref' => array(
                        'dep5' => 'file:dep5.json',
                        'dep4' => 'file:dep4.json',
                    ),
                    'main' => array(
                        'dep5',
                        'dep4',
                    ),
                ),
                'add' => array(
                    'key_from_dep3' => 'value from dep3',
                    'key_from_dep3_to_remove' => 'value from dep3 to remove',
                ),
            )
        )->setResolved(true);

        $xrefDep2 = new Xref('file', 'dep2.json');
        $xrefDep2->setData(
            array(
                'add' => array(
                    'key_from_dep2' => 'value from dep2'
                ),
                'remove' => array(
                    'key_from_dep3_to_remove' => '',
                ),
            )
        )->setResolved(true);

        $xrefMain = new Xref('file', 'main.json');
        $xrefMain->setData(
            array(
                'include' => array(
                    'xref' => array(
                        'dep3' => 'file:dep3.json',
                        'dep2' => 'file:dep2.json',
                    ),
                    'main' => array(
                        'dep2',
                        'dep3',
                    ),
                ),
                'add' => array(
                    'key_from_main' => 'value from main',
                    'key_from_dep4' => 'value from main',
                ),
                'remove' => array(
                    'unknown_key' => '',
                    'key_from_dep3_to_remove' => '',
                ),
            )
        )->setResolved(true);

        $treeCompiler = new TreeCompiler();
        $treeCompiler->getXrefs()->add($xrefDep2);
        $treeCompiler->getXrefs()->add($xrefDep3);
        $treeCompiler->getXrefs()->add($xrefDep4);
        $treeCompiler->getXrefs()->add($xrefDep5);

        $compiled = $treeCompiler->compileXref($xrefMain);

        $expected = array(
            'key_from_dep2' => 'value from dep2',
            'key_from_dep5' => 'value from dep5',
            'key_with_registered_token' => 'token resolved',
            'key_to_remove' => 'value from dep5',
            'key_from_dep4' => 'value from main',
            'key_from_dep3' => 'value from dep3',
            'key_from_main' => 'value from main',
        );

        $this->assertEquals($expected, $compiled);
    }
}