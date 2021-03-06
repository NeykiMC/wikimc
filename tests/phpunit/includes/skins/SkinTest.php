<?php

class SkinTest extends MediaWikiIntegrationTestCase {

	/**
	 * @covers Skin::getDefaultModules
	 */
	public function testGetDefaultModules() {
		$skin = $this->getMockBuilder( Skin::class )
			->setMethods( [ 'outputPage', 'setupSkinUserCss' ] )
			->getMock();

		$modules = $skin->getDefaultModules();
		$this->assertTrue( isset( $modules['core'] ), 'core key is set by default' );
		$this->assertTrue( isset( $modules['styles'] ), 'style key is set by default' );
	}

	/**
	 * @covers Skin::isResponsive
	 *
	 * @dataProvider provideSkinResponsiveOptions
	 */
	public function testIsResponsive( array $options, bool $expected ) {
		$skin = new class( $options ) extends Skin {
			/**
			 * @inheritDoc
			 */
			public function outputPage() {
			}
		};

		$this->assertSame( $expected, $skin->isResponsive() );
	}

	public function provideSkinResponsiveOptions() {
		yield 'responsive not set' => [
			[ 'name' => 'test' ],
			false
		];
		yield 'responsive false' => [
			[ 'name' => 'test', 'responsive' => false ],
			false
		];
		yield 'responsive true' => [
			[ 'name' => 'test', 'responsive' => true ],
			true
		];
	}

	/**
	 * @covers Skin::makeLink
	 */
	public function testMakeLinkLinkClass() {
		$skin = new class extends Skin {
			public function outputPage() {
			}
		};

		$link = $skin->makeLink(
			'test',
			[
				'text' => 'Test',
				'href' => '',
				'class' => [
					'class1',
					'class2'
				]
			],
			[ 'link-class' => 'link-class' ]
		);

		$this->assertHTMLEquals(
			'<a href="" class="class1 class2 link-class">Test</a>',
			$link
		);
	}
}
