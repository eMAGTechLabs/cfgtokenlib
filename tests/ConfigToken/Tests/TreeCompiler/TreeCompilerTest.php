<?php

namespace ConfigToken\Tests\TreeCompiler;


use ConfigToken\TreeCompiler;
use ConfigToken\TreeCompiler\Xref;

class TreeCompilerTest extends \PHPUnit_Framework_TestCase
{
    public function testTreeCompiler()
    {
        $xrefDep5 = new Xref('file', 'dep5.json');
        $xrefDep5->setData(
            array(
                "cluster" => array(
                    "dist" => array(
                        "dep" => "",
                        "master" => array(
                            "dist" => array(
                                "branch" => "master",
                                "config" => array(
                                    "apache" => array(
                                        "members" => array(
                                            "distapi.localhost" => array(
                                                "folder" => "/etc/apache2/sites-available/",
                                                "parameters" => array(
                                                    "custom_log" => "<root>/logs/custom.log",
                                                    "document_root" => "@json:/tree/sln_path_distapi",
                                                    "error_log" => "<root>/logs/error.log",
                                                    "php_error_log" => "<root>/logs/php_error.log",
                                                    "root_path_distapi" => "@json:/tree/root_path_distapi",
                                                    "server_name" => "@json:/tree/vhost_url_distapi",
                                                    "session_save_path" => "<root>/__sessions__",
                                                    "tmp_dir" => "<root>/__tmp__",
                                                    "upload_tmp_dir" => "<root>/__upload__",
                                                ),
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                        ),
                        "path" => "/var/www/",
                    ),
                ),
            )
        )->setResolved(true);

        $xrefDep4 = new Xref('file', 'dep4.json');
        $xrefDep4->setData(
            array(
                'include' => array(
                    'xref' => array(
                        'dep5' => 'file:dep5.json',
                    ),
                    'main' => array(
                        'dep5',
                    ),
                ),
                'add' => array(
                    "cluster" => array(
                        "dist" => array(
                            "master" => array(
                                "dist" => array(
                                    "config" => array(
                                        "apache" => array(
                                            "options" => array(
                                                "language" => "php",
                                                "secure" => "False",
                                            )
                                        )
                                    )
                                )
                            )
                        )
                    )
                ),
                'remove' => array(
                    "cluster" => array(
                        "dist" => array(
                            "master" => array(
                                "dist" => array(
                                    "branch" => "REMOVE",
                                    "config" => array(
                                        "apache" => array(
                                            "members" => array(
                                                "distapi.localhost" => array(
                                                    "parameters" => array(
                                                        "custom_log" => "REMOVE",
                                                    ),
                                                ),
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
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
                    "cluster" => array(
                        "dist" => array(
                            "master" => array(
                                "dist" => array(
                                    "config" => array(
                                        "app" => array(
                                            "members" => array(
                                                "config.ini" => array(
                                                    "folder" => "<release-next>/application/configs/",
                                                    "parameters" => array(
                                                        "api_password_distapi_a" => "@json:/tree/api_password_distapi_a",
                                                        "api_password_distapi_b" => "@json:/tree/api_password_distapi_b",
                                                        "api_password_distapi_c" => "@json:/tree/api_password_distapi_c",
                                                        "api_password_distapi_d" => "@json:/tree/api_password_distapi_d",
                                                        "a_ip" => "@json:/tree/ip_a",
                                                        "b_ip" => "@json:/tree/ip_b",
                                                        "c_ip" => "@json:/tree/ip_c",
                                                        "d_ip" => "@json:/tree/ip_d",
                                                    ),
                                                    "tpl" => "config.ini"
                                                ),
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            )
        )->setResolved(true);

        $xrefDep2 = new Xref('file', 'dep2.json');
        $xrefDep2->setData(
            array(
                'add' => array(

                ),
                'remove' => array(

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

                ),
                'remove' => array(

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

        );

        $this->assertEquals($expected, $compiled);
    }
}