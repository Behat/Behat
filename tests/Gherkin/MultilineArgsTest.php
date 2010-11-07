<?php

use Everzet\Gherkin\Node\TableNode;
use Everzet\Gherkin\Node\PyStringNode;

/*
 * This file is part of the Gherkin.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Gherkin Multiline Arguments Nodes Test.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class MultilineArgsTest extends \PHPUnit_Framework_TestCase
{
    public function testHashTable()
    {
        $table = new TableNode(<<<TABLE
| username | password |
| everzet  | qwerty   |
| antono   | pa\$sword|
TABLE
        );

        $this->assertEquals(
            array(
                array('username' => 'everzet', 'password' => 'qwerty')
              , array('username' => 'antono', 'password' => 'pa$sword')
            )
          , $table->getHash()
        );

        $table = new TableNode(<<<TABLE
| username | password |
|          | qwerty   |
| antono   |          |
|          |          |
TABLE
        );

        $this->assertEquals(
            array(
                array('username' => '', 'password' => 'qwerty')
              , array('username' => 'antono', 'password' => '')
              , array('username' => '', 'password' => '')
            )
          , $table->getHash()
        );
    }

    public function testRowsHashTable()
    {
        $table = new TableNode(<<<TABLE
| username | everzet  |
| password | qwerty   |
| uid      | 35       |
TABLE
        );

        $this->assertEquals(array('username' => 'everzet', 'password' => 'qwerty', 'uid' => '35'), $table->getRowsHash());
    }

    public function testTableFromArrayCreation()
    {
        $table1 = new TableNode();
        $table1->addRow(array('username', 'password'));
        $table1->addRow(array('everzet', 'qwerty'));
        $table1->addRow(array('antono', 'pa$sword'));

        $table2 = new TableNode(<<<TABLE
| username | password |
| everzet  | qwerty   |
| antono   | pa\$sword|
TABLE
        );

        $this->assertEquals($table2->getRows(), $table1->getRows());

        $this->assertEquals(
            array(
                array('username' => 'everzet', 'password' => 'qwerty')
              , array('username' => 'antono', 'password' => 'pa$sword')
            )
          , $table1->getHash()
        );

        $this->assertEquals(
            array('username' => 'password', 'everzet' => 'qwerty', 'antono' => 'pa$sword')
          , $table2->getRowsHash()
        );
    }

    public function testPyString()
    {
        $string = new PyStringNode(<<<STRING
Hello,
  My little
    Friend
      =)
STRING
        );

        $this->assertEquals(<<<STRING
Hello,
My little
Friend
  =)
STRING
          , (string) $string
        );
    }

    public function testPyStringFromLinesCreation()
    {
        $string = new PyStringNode();
        $string->addLine('Hello,');
        $string->addLine('  My little');
        $string->addLine('    Friend');
        $string->addLine('      =)');

        $this->assertEquals(<<<STRING
Hello,
My little
Friend
  =)
STRING
          , (string) $string
        );
    }
}
