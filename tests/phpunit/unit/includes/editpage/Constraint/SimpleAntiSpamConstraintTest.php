<?php
/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 */

use MediaWiki\EditPage\Constraint\IEditConstraint;
use MediaWiki\EditPage\Constraint\SimpleAntiSpamConstraint;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;

/**
 * Tests the SimpleAntiSpamConstraint
 *
 * @author DannyS712
 *
 * @covers \MediaWiki\EditPage\Constraint\SimpleAntiSpamConstraint
 */
class SimpleAntiSpamConstraintTest extends MediaWikiUnitTestCase {

	public function testPass() {
		$logger = new NullLogger();
		$user = $this->createMock( User::class );
		$title = $this->createMock( Title::class );

		$constraint = new SimpleAntiSpamConstraint(
			$logger,
			'',
			$user,
			$title
		);
		$this->assertSame(
			IEditConstraint::CONSTRAINT_PASSED,
			$constraint->checkConstraint()
		);

		$status = $constraint->getLegacyStatus();
		$this->assertTrue( $status->isGood() );
	}

	public function testFailure() {
		$logger = new TestLogger( true );
		$user = $this->createMock( User::class );
		$user->expects( $this->once() )
			->method( 'getName' )
			->willReturn( 'UserNameGoesHere' );
		$title = $this->createMock( Title::class );
		$title->expects( $this->once() )
			->method( 'getPrefixedText' )
			->willReturn( 'TitlePrefixedTextGoesHere' );

		$constraint = new SimpleAntiSpamConstraint(
			$logger,
			'SpamContent',
			$user,
			$title
		);
		$this->assertSame(
			IEditConstraint::CONSTRAINT_FAILED,
			$constraint->checkConstraint()
		);

		$this->assertSame( [
			[
				LogLevel::DEBUG,
				'{name} editing "{title}" submitted bogus field "{input}"'
			],
		], $logger->getBuffer() );
		$logger->clearBuffer();

		$status = $constraint->getLegacyStatus();
		$this->assertFalse( $status->isGood() );
		$this->assertSame(
			IEditConstraint::AS_SPAM_ERROR,
			$status->getValue()
		);
	}

}