<?php
namespace Grav\Plugin\VideoEmbed\Tests\VideoEmbed;

class ServiceAbstractTest extends \PHPUnit_Framework_TestCase
{
    const CLASS_NAME = '\\Grav\\Plugin\\VideoEmbed\\ServiceAbstract';
    /**
     * @dataProvider dpConstruct
     */
    public function testConstruct(array $config)
    {
        $mock = $this->getMockBuilder(self::CLASS_NAME)
            ->setMethods(['createDOMNode'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        if (empty($config['embed_html_attr'])) {
            $config['embed_html_attr'] = [];
        }

        if (
            !empty($config['embed_html_attr'])
            && isset($config['embed_html_attr']['allowfullscreen'])
            && $config['embed_html_attr']['allowfullscreen']
        ) {
            $config['embed_html_attr'] = array_merge(
                $config['embed_html_attr'],
                ['webkitallowfullscreen' => true, 'mozallowfullscreen' => true]
            );
        }

        $mock->expects($this->once())
            ->method('createDOMNode')
            ->with(
                $this->isInstanceOf('\DOMDocument'),
                $this->equalTo('iframe'),
                $this->equalTo($config['embed_html_attr'])
            );

        $relection = new \ReflectionClass(self::CLASS_NAME);
        $constructor = $relection->getConstructor();
        $constructor->invoke($mock, $config);
    }

    /**
     * @dataProvider dpProcessHtml
     */
    public function testProcessHtml($config, $container, $before, $expectedAfter)
    {
        $mock = $this->getMockBuilder(self::CLASS_NAME)
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

    /**
     * @dataProvider dpPrepareStandardEmbed
     */
    public function testPrepareStandardEmbed($embedOptions, $expectedSrc, $embedUrl, $urlQuery, array $ignore = [])
    {
        $mock = $this->getMockBuilder(self::CLASS_NAME)
            ->setConstructorArgs([['embed_options' => $embedOptions]])
            ->enableOriginalConstructor()
            ->getMockForAbstractClass();

        $method = new \ReflectionMethod(self::CLASS_NAME, 'prepareStandardEmbed');
        $method->setAccessible(true);

        $this->assertEquals(
            $expectedSrc,
            $method->invoke($mock, $embedUrl, $urlQuery, $ignore, $expectedSrc)
                ->getAttribute('src')
        );
    }

    public function dpConstruct()
    {
        return [
            [[]],
            [[
                'embed_html_attr' => [
                    'allowfullscreen' => false
                ]
            ]],
            [[
                'embed_html_attr' => [
                    'allowfullscreen' => true
                ]
            ]]
        ];
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

    public function dpPrepareStandardEmbed()
    {
        return [
            [
                ['descr' => 'desc'],
                '/embed_url1?descr=desc&name=embed1',
                '/embed_url1',
                '?name=embed1'
            ],
            [
                ['descr' => 'test', 'ignoringAttr' => 'wtf'],
                '/embed_url2?descr=test',
                '/embed_url2',
                '?&',
                ['ignoringAttr']
            ]
        ];
    }
}
