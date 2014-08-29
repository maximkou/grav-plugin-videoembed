<?php
namespace Grav\Plugin\VideoEmbed\Tests\VideoEmbed;

class ServiceAbstractTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dpProcessHtml
     */
    public function testProcessHtml($config, $container, $before, $expectedAfter)
    {
        $mock = $this->getMockBuilder('\\Grav\\Plugin\\VideoEmbed\\ServiceAbstract')
            ->setMethods(['getEmbedNodes', 'getRegExpression'])
            ->setConstructorArgs([ $config ])
            ->getMockForAbstractClass();

        $mock->expects($this->any())
            ->method('getRegExpression')
            ->willReturn(
                '\[test\_url\]'
            );

        $doc = new \DOMDocument();
        $div = $doc->createElement('div', 'testOK');
        $mock->expects($this->any())
            ->method('getEmbedNodes')
            ->willReturn($div);

        $result = html_entity_decode($mock->processHtml($before, $container));
        $this->assertEquals($expectedAfter, $result);
    }

    public function dpProcessHtml()
    {
        $data = [];

        $doc = new \DOMDocument();
        $c = $doc->createElement('div');
        $c->setAttribute('class', 'container');

        foreach (glob(__DIR__.'/../_data/VideoEmbed/ServiceAbstract/*', GLOB_ONLYDIR) as $caseDir) {
            $data[] = [
                json_decode(file_get_contents($caseDir.'/youtube.json'), true),
                preg_match('/\_container$/', $caseDir) ? $c : null,
                file_get_contents($caseDir.'/page.html'),
                file_get_contents($caseDir.'/page_expected.html')
            ];
        }

        return $data;
    }
}
