<?php
namespace TYPO3\FLOW3\Tests\Unit\Cache\Backend;

/*                                                                        *
 * This script belongs to the FLOW3 framework.                            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * Testcase for the cache to file backend
 *
 */
class FileBackendTest extends \TYPO3\FLOW3\Tests\UnitTestCase {

	/**
	 */
	public function setUp() {
		\vfsStreamWrapper::register();
		\vfsStreamWrapper::setRoot(new \vfsStreamDirectory('Foo'));
	}

	/**
	 * @test
	 * @expectedException \TYPO3\FLOW3\Cache\Exception
	 */
	public function setCacheThrowsExceptionOnNonWritableDirectory() {
		$mockCache = $this->getMock('TYPO3\FLOW3\Cache\Frontend\AbstractFrontend', array(), array(), '', FALSE);

		$mockEnvironment = $this->getMock('TYPO3\FLOW3\Utility\Environment', array(), array(), '', FALSE);
		$mockEnvironment->expects($this->any())->method('getPathToTemporaryDirectory')->will($this->returnValue('http://localhost/'));

		$backend = $this->getMock('TYPO3\FLOW3\Cache\Backend\FileBackend', array('dummy'), array(), '', FALSE);
		$backend->injectEnvironment($mockEnvironment);

		$backend->setCache($mockCache);
	}

	/**
	 * @test
	 */
	public function getCacheDirectoryReturnsTheCurrentCacheDirectory() {
		$mockCache = $this->getMock('TYPO3\FLOW3\Cache\Frontend\AbstractFrontend', array(), array(), '', FALSE);
		$mockCache->expects($this->any())->method('getIdentifier')->will($this->returnValue('SomeCache'));

		$mockEnvironment = $this->getMock('TYPO3\FLOW3\Utility\Environment', array(), array(), '', FALSE);
		$mockEnvironment->expects($this->any())->method('getPathToTemporaryDirectory')->will($this->returnValue('vfs://Foo/'));

			// We need to create the directory here because vfs doesn't support touch() which is used by
			// createDirectoryRecursively() in the setCache method.
		mkdir ('vfs://Foo/Cache');

		$backend = $this->getMock('TYPO3\FLOW3\Cache\Backend\FileBackend', array('dummy'), array(), '', FALSE);
		$backend->injectEnvironment($mockEnvironment);
		$backend->setCache($mockCache);

		$this->assertEquals('vfs://Foo/Cache/Data/SomeCache/', $backend->getCacheDirectory());
	}

	/**
	 * @test
	 */
	public function aDedicatedCacheDirectoryIsUsedForCodeCaches() {
		$mockEnvironment = $this->getMock('TYPO3\FLOW3\Utility\Environment', array(), array(), '', FALSE);
		$mockEnvironment->expects($this->any())->method('getPathToTemporaryDirectory')->will($this->returnValue('vfs://Foo/'));

			// We need to create the directory here because vfs doesn't support touch() which is used by
			// createDirectoryRecursively() in the setCache method.
		mkdir ('vfs://Foo/Cache');

		$backend = $this->getMock('TYPO3\FLOW3\Cache\Backend\FileBackend', array('dummy'), array(), '', FALSE);
		$backend->injectEnvironment($mockEnvironment);

		new \TYPO3\FLOW3\Cache\Frontend\PhpFrontend('SomeCache', $backend);

		$this->assertEquals('vfs://Foo/Cache/Code/SomeCache/', $backend->getCacheDirectory());
	}

	/**
	 * @test
	 * @expectedException \TYPO3\FLOW3\Cache\Exception\InvalidDataException
	 */
	public function setThrowsExceptionIfDataIsNotAString() {
		$mockCache = $this->getMock('TYPO3\FLOW3\Cache\Frontend\AbstractFrontend', array(), array(), '', FALSE);

		$mockEnvironment = $this->getMock('TYPO3\FLOW3\Utility\Environment', array(), array(), '', FALSE);
		$mockEnvironment->expects($this->any())->method('getPathToTemporaryDirectory')->will($this->returnValue('vfs://Foo/'));

		$backend = $this->getMock('TYPO3\FLOW3\Cache\Backend\FileBackend', array('dummy'), array(), '', FALSE);
		$backend->injectEnvironment($mockEnvironment);
		$backend->setCache($mockCache);
		$backend->set('SomeIdentifier', array('not a string'));
	}

	/**
	 * @test
	 */
	public function setReallySavesToTheSpecifiedDirectory() {
		$mockCache = $this->getMock('TYPO3\FLOW3\Cache\Frontend\AbstractFrontend', array(), array(), '', FALSE);
		$mockCache->expects($this->atLeastOnce())->method('getIdentifier')->will($this->returnValue('UnitTestCache'));

		$mockEnvironment = $this->getMock('TYPO3\FLOW3\Utility\Environment', array(), array(), '', FALSE);
		$mockEnvironment->expects($this->any())->method('getMaximumPathLength')->will($this->returnValue(255));
		$mockEnvironment->expects($this->any())->method('getPathToTemporaryDirectory')->will($this->returnValue('vfs://Foo/'));

		$data = 'some data' . microtime();
		$entryIdentifier = 'BackendFileTest';
		$pathAndFilename = 'vfs://Foo/Cache/Data/UnitTestCache/' . $entryIdentifier;

		$backend = $this->getMock('TYPO3\FLOW3\Cache\Backend\FileBackend', array('dummy'), array(), '', FALSE);
		$backend->injectEnvironment($mockEnvironment);
		$backend->setCache($mockCache);

		$backend->set($entryIdentifier, $data);

		$this->assertFileExists($pathAndFilename);
		$retrievedData = file_get_contents($pathAndFilename, NULL, NULL, 0, strlen($data));
		$this->assertEquals($data, $retrievedData);
	}

	/**
	 * @test
	 */
	public function setOverwritesAnAlreadyExistingCacheEntryForTheSameIdentifier() {
		$mockCache = $this->getMock('TYPO3\FLOW3\Cache\Frontend\AbstractFrontend', array(), array(), '', FALSE);
		$mockCache->expects($this->atLeastOnce())->method('getIdentifier')->will($this->returnValue('UnitTestCache'));

		$mockEnvironment = $this->getMock('TYPO3\FLOW3\Utility\Environment', array(), array(), '', FALSE);
		$mockEnvironment->expects($this->any())->method('getMaximumPathLength')->will($this->returnValue(255));
		$mockEnvironment->expects($this->any())->method('getPathToTemporaryDirectory')->will($this->returnValue('vfs://Foo/'));

		$data1 = 'some data' . microtime();
		$data2 = 'some data' . microtime();
		$entryIdentifier = 'BackendFileRemoveBeforeSetTest';

		$backend = $this->getMock('TYPO3\FLOW3\Cache\Backend\FileBackend', array('dummy'), array(), '', FALSE);
		$backend->injectEnvironment($mockEnvironment);
		$backend->setCache($mockCache);

		$backend->set($entryIdentifier, $data1, array(), 500);
		$backend->set($entryIdentifier, $data2, array(), 200);

		$pathAndFilename = 'vfs://Foo/Cache/Data/UnitTestCache/' . $entryIdentifier;
		$this->assertFileExists($pathAndFilename);
		$retrievedData = file_get_contents($pathAndFilename, NULL, NULL, 0, strlen($data2));
		$this->assertEquals($data2, $retrievedData);
	}

	/**
	 * @test
	 */
	public function setAlsoSavesSpecifiedTags() {
		$mockCache = $this->getMock('TYPO3\FLOW3\Cache\Frontend\AbstractFrontend', array(), array(), '', FALSE);
		$mockCache->expects($this->atLeastOnce())->method('getIdentifier')->will($this->returnValue('UnitTestCache'));

		$mockEnvironment = $this->getMock('TYPO3\FLOW3\Utility\Environment', array(), array(), '', FALSE);
		$mockEnvironment->expects($this->any())->method('getMaximumPathLength')->will($this->returnValue(255));
		$mockEnvironment->expects($this->any())->method('getPathToTemporaryDirectory')->will($this->returnValue('vfs://Foo/'));

		$data = 'some data' . microtime();
		$entryIdentifier = 'BackendFileRemoveBeforeSetTest';

		$backend = $this->getMock('TYPO3\FLOW3\Cache\Backend\FileBackend', array('dummy'), array(), '', FALSE);
		$backend->injectEnvironment($mockEnvironment);
		$backend->setCache($mockCache);

		$backend->set($entryIdentifier, $data, array('Tag1', 'Tag2'));

		$pathAndFilename = 'vfs://Foo/Cache/Data/UnitTestCache/' . $entryIdentifier;
		$this->assertFileExists($pathAndFilename);
		$retrievedData = file_get_contents($pathAndFilename, NULL, NULL, (strlen($data) + \TYPO3\FLOW3\Cache\Backend\FileBackend::EXPIRYTIME_LENGTH), 9);
		$this->assertEquals('Tag1 Tag2', $retrievedData);
	}

	/**
	 * @test
	 * @expectedException \TYPO3\FLOW3\Cache\Exception
	 */
	public function setThrowsExceptionIfCachePathLengthExceedsMaximumPathLength() {
		$cacheIdentifier = 'UnitTestCache';
		$mockCache = $this->getMock('TYPO3\FLOW3\Cache\Frontend\AbstractFrontend', array(), array(), '', FALSE);
		$mockCache->expects($this->atLeastOnce())->method('getIdentifier')->will($this->returnValue($cacheIdentifier));

		$mockEnvironment = $this->getMock('TYPO3\FLOW3\Utility\Environment', array(), array(), '', FALSE);
		$mockEnvironment->expects($this->any())->method('getMaximumPathLength')->will($this->returnValue(5));
		$mockEnvironment->expects($this->any())->method('getPathToTemporaryDirectory')->will($this->returnValue('vfs://Foo/'));

		$entryIdentifier = 'BackendFileTest';

		$backend = $this->getMock('TYPO3\FLOW3\Cache\Backend\FileBackend', array('setTag'), array(), '', FALSE);
		$backend->injectEnvironment($mockEnvironment);
		$backend->setCache($mockCache);

		$backend->set($entryIdentifier, 'cache data');
	}

	/**
	 * @test
	 */
	public function setCacheDetectsAndLoadsAFrozenCache() {
		$mockCache = $this->getMock('TYPO3\FLOW3\Cache\Frontend\AbstractFrontend', array(), array(), '', FALSE);
		$mockCache->expects($this->atLeastOnce())->method('getIdentifier')->will($this->returnValue('UnitTestCache'));

		$mockEnvironment = $this->getMock('TYPO3\FLOW3\Utility\Environment', array(), array(), '', FALSE);
		$mockEnvironment->expects($this->any())->method('getMaximumPathLength')->will($this->returnValue(255));
		$mockEnvironment->expects($this->any())->method('getPathToTemporaryDirectory')->will($this->returnValue('vfs://Foo/'));

		$data = 'some data' . microtime();
		$entryIdentifier = 'BackendFileTest';

		$backend = $this->getMock('TYPO3\FLOW3\Cache\Backend\FileBackend', array('dummy'), array(), '', FALSE);
		$backend->injectEnvironment($mockEnvironment);
		$backend->setCache($mockCache);

		$backend->set($entryIdentifier, $data);

		$backend->freeze();

		unset($backend);

		$backend = $this->getMock('TYPO3\FLOW3\Cache\Backend\FileBackend', array('dummy'), array(), '', FALSE);
		$backend->injectEnvironment($mockEnvironment);
		$backend->setCache($mockCache);

		$this->assertTrue($backend->isFrozen());
		$this->assertEquals($data, $backend->get($entryIdentifier));
	}

	/**
	 * @test
	 */
	public function getReturnsContentOfTheCorrectCacheFile() {
		$mockCache = $this->getMock('TYPO3\FLOW3\Cache\Frontend\AbstractFrontend', array(), array(), '', FALSE);
		$mockCache->expects($this->atLeastOnce())->method('getIdentifier')->will($this->returnValue('UnitTestCache'));

		$mockEnvironment = $this->getMock('TYPO3\FLOW3\Utility\Environment', array(), array(), '', FALSE);
		$mockEnvironment->expects($this->any())->method('getMaximumPathLength')->will($this->returnValue(255));
		$mockEnvironment->expects($this->any())->method('getPathToTemporaryDirectory')->will($this->returnValue('vfs://Foo/'));

		$backend = $this->getMock('TYPO3\FLOW3\Cache\Backend\FileBackend', array('setTag'), array(), '', FALSE);
		$backend->injectEnvironment($mockEnvironment);
		$backend->setCache($mockCache);

		$entryIdentifier = 'BackendFileTest';

		$data = 'some data' . microtime();
		$backend->set($entryIdentifier, $data, array(), 500);

		$data = 'some other data' . microtime();
		$backend->set($entryIdentifier, $data, array(), 100);

		$loadedData = $backend->get($entryIdentifier);
		$this->assertEquals($data, $loadedData);
	}

	/**
	 * @test
	 */
	public function getReturnsFalseForExpiredEntries() {
		$mockCache = $this->getMock('TYPO3\FLOW3\Cache\Frontend\AbstractFrontend', array(), array(), '', FALSE);
		$mockCache->expects($this->atLeastOnce())->method('getIdentifier')->will($this->returnValue('UnitTestCache'));

		$mockEnvironment = $this->getMock('TYPO3\FLOW3\Utility\Environment', array(), array(), '', FALSE);
		$mockEnvironment->expects($this->any())->method('getMaximumPathLength')->will($this->returnValue(255));
		$mockEnvironment->expects($this->any())->method('getPathToTemporaryDirectory')->will($this->returnValue('vfs://Foo/'));

		$backend = $this->getMock('TYPO3\FLOW3\Cache\Backend\FileBackend', array('isCacheFileExpired'), array(), '', FALSE);
		$backend->expects($this->once())->method('isCacheFileExpired')->with('vfs://Foo/Cache/Data/UnitTestCache/ExpiredEntry')->will($this->returnValue(TRUE));
		$backend->injectEnvironment($mockEnvironment);
		$backend->setCache($mockCache);

		$this->assertFalse($backend->get('ExpiredEntry'));
	}

	/**
	 * @test
	 */
	public function getDoesNotCheckIfAnEntryIsExpiredIfTheCacheIsFrozen() {
		$mockCache = $this->getMock('TYPO3\FLOW3\Cache\Frontend\AbstractFrontend', array(), array(), '', FALSE);
		$mockCache->expects($this->atLeastOnce())->method('getIdentifier')->will($this->returnValue('UnitTestCache'));

		$mockEnvironment = $this->getMock('TYPO3\FLOW3\Utility\Environment', array(), array(), '', FALSE);
		$mockEnvironment->expects($this->any())->method('getMaximumPathLength')->will($this->returnValue(255));
		$mockEnvironment->expects($this->any())->method('getPathToTemporaryDirectory')->will($this->returnValue('vfs://Foo/'));

		$backend = $this->getMock('TYPO3\FLOW3\Cache\Backend\FileBackend', array('isCacheFileExpired'), array(), '', FALSE);
		$backend->injectEnvironment($mockEnvironment);
		$backend->setCache($mockCache);

		$backend->expects($this->once())->method('isCacheFileExpired');

		$backend->set('foo', 'some data');
		$backend->freeze();
		$this->assertEquals('some data', $backend->get('foo'));
		$this->assertFalse($backend->get('bar'));
	}

	/**
	 * @test
	 */
	public function hasReturnsTrueIfAnEntryExists() {
		$mockEnvironment = $this->getMock('TYPO3\FLOW3\Utility\Environment', array(), array(), '', FALSE);
		$mockEnvironment->expects($this->any())->method('getMaximumPathLength')->will($this->returnValue(255));
		$mockEnvironment->expects($this->any())->method('getPathToTemporaryDirectory')->will($this->returnValue('vfs://Foo/'));

		$mockCache = $this->getMock('TYPO3\FLOW3\Cache\Frontend\AbstractFrontend', array(), array(), '', FALSE);
		$mockCache->expects($this->atLeastOnce())->method('getIdentifier')->will($this->returnValue('UnitTestCache'));

		$backend = $this->getMock('TYPO3\FLOW3\Cache\Backend\FileBackend', array('dummy'), array(), '', FALSE);
		$backend->injectEnvironment($mockEnvironment);
		$backend->setCache($mockCache);

		$entryIdentifier = 'BackendFileTest';

		$data = 'some data' . microtime();
		$backend->set($entryIdentifier, $data);

		$this->assertTrue($backend->has($entryIdentifier), 'has() did not return TRUE.');
		$this->assertFalse($backend->has($entryIdentifier . 'Not'), 'has() did not return FALSE.');
	}

	/**
	 * @test
	 */
	public function hasReturnsFalseForExpiredEntries() {
		$backend = $this->getMock('TYPO3\FLOW3\Cache\Backend\FileBackend', array('isCacheFileExpired'), array(), '', FALSE);
		$backend->expects($this->exactly(2))->method('isCacheFileExpired')->will($this->onConsecutiveCalls(TRUE, FALSE));

		$this->assertFalse($backend->has('foo'));
		$this->assertTrue($backend->has('bar'));
	}

	/**
	 * @test
	 */
	public function hasDoesNotCheckIfAnEntryIsExpiredIfTheCacheIsFrozen() {
		$mockCache = $this->getMock('TYPO3\FLOW3\Cache\Frontend\AbstractFrontend', array(), array(), '', FALSE);
		$mockCache->expects($this->atLeastOnce())->method('getIdentifier')->will($this->returnValue('UnitTestCache'));

		$mockEnvironment = $this->getMock('TYPO3\FLOW3\Utility\Environment', array(), array(), '', FALSE);
		$mockEnvironment->expects($this->any())->method('getMaximumPathLength')->will($this->returnValue(255));
		$mockEnvironment->expects($this->any())->method('getPathToTemporaryDirectory')->will($this->returnValue('vfs://Foo/'));

		$backend = $this->getMock('TYPO3\FLOW3\Cache\Backend\FileBackend', array('isCacheFileExpired'), array(), '', FALSE);
		$backend->injectEnvironment($mockEnvironment);
		$backend->setCache($mockCache);

		$backend->expects($this->once())->method('isCacheFileExpired'); // Indirectly called by freeze() -> get()

		$backend->set('foo', 'some data');
		$backend->freeze();
		$this->assertTrue($backend->has('foo'));
		$this->assertFalse($backend->has('bar'));
	}

	/**
	 * @test
	 *
	 */
	public function removeReallyRemovesACacheEntry() {
		$mockCache = $this->getMock('TYPO3\FLOW3\Cache\Frontend\AbstractFrontend', array(), array(), '', FALSE);
		$mockCache->expects($this->atLeastOnce())->method('getIdentifier')->will($this->returnValue('UnitTestCache'));

		$mockEnvironment = $this->getMock('TYPO3\FLOW3\Utility\Environment', array(), array(), '', FALSE);
		$mockEnvironment->expects($this->any())->method('getMaximumPathLength')->will($this->returnValue(255));
		$mockEnvironment->expects($this->any())->method('getPathToTemporaryDirectory')->will($this->returnValue('vfs://Foo/'));

		$data = 'some data' . microtime();
		$entryIdentifier = 'BackendFileTest';
		$pathAndFilename = 'vfs://Foo/Cache/Data/UnitTestCache/' . $entryIdentifier;

		$backend = $this->getMock('TYPO3\FLOW3\Cache\Backend\FileBackend', array('dummy'), array(), '', FALSE);
		$backend->injectEnvironment($mockEnvironment);
		$backend->setCache($mockCache);

		$backend->set($entryIdentifier, $data);
		$this->assertFileExists($pathAndFilename);

		$backend->remove($entryIdentifier);
		$this->assertFileNotExists($pathAndFilename);
	}

	/**
	 */
	public function invalidEntryIdentifiers() {
		return array(
			'trailing slash' => array('/myIdentifer'),
			'trailing dot and slash' => array('./myIdentifer'),
			'trailing two dots and slash' => array('../myIdentifier'),
			'trailing with multiple dots and slashes' => array('.././../myIdentifier'),
			'slash in middle part' => array('my/Identifier'),
			'dot and slash in middle part' => array('my./Identifier'),
			'two dots and slash in middle part' => array('my../Identifier'),
			'multiple dots and slashes in middle part' => array('my.././../Identifier'),
			'pending slash' => array('myIdentifier/'),
			'pending dot and slash' => array('myIdentifier./'),
			'pending dots and slash' => array('myIdentifier../'),
			'pending multiple dots and slashes' => array('myIdentifier.././../'),
		);
	}

	/**
	 * @test
	 * @dataProvider invalidEntryIdentifiers
	 * @expectedException InvalidArgumentException
	 */
	public function setThrowsExceptionForInvalidIdentifier($identifier) {
		$mockCache = $this->getMock('TYPO3\FLOW3\Cache\Frontend\AbstractFrontend', array(), array(), '', FALSE);
		$mockCache->expects($this->atLeastOnce())->method('getIdentifier')->will($this->returnValue('UnitTestCache'));

		$mockEnvironment = $this->getMock('TYPO3\FLOW3\Utility\Environment', array(), array(), '', FALSE);
		$mockEnvironment->expects($this->any())->method('getMaximumPathLength')->will($this->returnValue(255));
		$mockEnvironment->expects($this->any())->method('getPathToTemporaryDirectory')->will($this->returnValue('vfs://Foo/'));

		$backend = $this->getMock('TYPO3\FLOW3\Cache\Backend\FileBackend', array('dummy'), array(), '', FALSE);
		$backend->injectEnvironment($mockEnvironment);
		$backend->setCache($mockCache);

		$backend->set($identifier, 'cache data', array());
	}

	/**
	 * @test
	 * @dataProvider invalidEntryIdentifiers
	 * @expectedException InvalidArgumentException
	 */
	public function getThrowsExceptionForInvalidIdentifier($identifier) {
		$mockCache = $this->getMock('TYPO3\FLOW3\Cache\Frontend\AbstractFrontend', array(), array(), '', FALSE);
		$mockCache->expects($this->atLeastOnce())->method('getIdentifier')->will($this->returnValue('UnitTestCache'));

		$mockEnvironment = $this->getMock('TYPO3\FLOW3\Utility\Environment', array(), array(), '', FALSE);
		$mockEnvironment->expects($this->any())->method('getMaximumPathLength')->will($this->returnValue(255));
		$mockEnvironment->expects($this->any())->method('getPathToTemporaryDirectory')->will($this->returnValue('vfs://Foo/'));

		$backend = $this->getMock('TYPO3\FLOW3\Cache\Backend\FileBackend', array('isCacheFileExpired'), array(), '', FALSE);
		$backend->injectEnvironment($mockEnvironment);
		$backend->setCache($mockCache);

		$backend->get($identifier);
	}

	/**
	 * @test
	 * @dataProvider invalidEntryIdentifiers
	 * @expectedException InvalidArgumentException
	 */
	public function hasThrowsExceptionForInvalidIdentifier($identifier) {
		$backend = $this->getMock('TYPO3\FLOW3\Cache\Backend\FileBackend', array('dummy'), array(), '', FALSE);

		$backend->has($identifier);
	}

	/**
	 * @test
	 * @dataProvider invalidEntryIdentifiers
	 * @expectedException InvalidArgumentException
	 */
	public function removeThrowsExceptionForInvalidIdentifier($identifier) {
		$mockCache = $this->getMock('TYPO3\FLOW3\Cache\Frontend\AbstractFrontend', array(), array(), '', FALSE);
		$mockCache->expects($this->atLeastOnce())->method('getIdentifier')->will($this->returnValue('UnitTestCache'));

		$mockEnvironment = $this->getMock('TYPO3\FLOW3\Utility\Environment', array(), array(), '', FALSE);
		$mockEnvironment->expects($this->any())->method('getMaximumPathLength')->will($this->returnValue(255));
		$mockEnvironment->expects($this->any())->method('getPathToTemporaryDirectory')->will($this->returnValue('vfs://Foo/'));

		$backend = $this->getMock('TYPO3\FLOW3\Cache\Backend\FileBackend', array('dummy'), array(), '', FALSE);
		$backend->injectEnvironment($mockEnvironment);
		$backend->setCache($mockCache);

		$backend->remove($identifier);
	}

	/**
	 * @test
	 * @dataProvider invalidEntryIdentifiers
	 * @expectedException InvalidArgumentException
	 */
	public function requireOnceThrowsExceptionForInvalidIdentifier($identifier) {
		$mockCache = $this->getMock('TYPO3\FLOW3\Cache\Frontend\AbstractFrontend', array(), array(), '', FALSE);
		$mockCache->expects($this->atLeastOnce())->method('getIdentifier')->will($this->returnValue('UnitTestCache'));

		$mockEnvironment = $this->getMock('TYPO3\FLOW3\Utility\Environment', array(), array(), '', FALSE);
		$mockEnvironment->expects($this->any())->method('getMaximumPathLength')->will($this->returnValue(255));
		$mockEnvironment->expects($this->any())->method('getPathToTemporaryDirectory')->will($this->returnValue('vfs://Foo/'));

		$backend = $this->getMock('TYPO3\FLOW3\Cache\Backend\FileBackend', array('dummy'), array(), '', FALSE);
		$backend->injectEnvironment($mockEnvironment);
		$backend->setCache($mockCache);

		$backend->requireOnce($identifier);
	}

	/**
	 * @test
	 */
	public function requireOnceIncludesAndReturnsResultOfIncludedPhpFile() {
		$mockCache = $this->getMock('TYPO3\FLOW3\Cache\Frontend\AbstractFrontend', array(), array(), '', FALSE);
		$mockCache->expects($this->atLeastOnce())->method('getIdentifier')->will($this->returnValue('UnitTestCache'));

		$mockEnvironment = $this->getMock('TYPO3\FLOW3\Utility\Environment', array(), array(), '', FALSE);
		$mockEnvironment->expects($this->any())->method('getMaximumPathLength')->will($this->returnValue(255));
		$mockEnvironment->expects($this->any())->method('getPathToTemporaryDirectory')->will($this->returnValue('vfs://Foo/'));

		$backend = $this->getMock('TYPO3\FLOW3\Cache\Backend\FileBackend', array('dummy'), array(), '', FALSE);
		$backend->injectEnvironment($mockEnvironment);
		$backend->setCache($mockCache);

		$entryIdentifier = 'SomePhpEntry';

		$data = '<?php return "foo"; ?>';
		$backend->set($entryIdentifier, $data);

		$loadedData = $backend->requireOnce($entryIdentifier);
		$this->assertEquals('foo', $loadedData);
	}

	/**
	 * @test
	 */
	public function requireOnceDoesNotCheckExpiryTimeIfBackendIsFrozen() {
		$mockCache = $this->getMock('TYPO3\FLOW3\Cache\Frontend\AbstractFrontend', array(), array(), '', FALSE);
		$mockCache->expects($this->atLeastOnce())->method('getIdentifier')->will($this->returnValue('UnitTestCache'));

		$mockEnvironment = $this->getMock('TYPO3\FLOW3\Utility\Environment', array(), array(), '', FALSE);
		$mockEnvironment->expects($this->any())->method('getMaximumPathLength')->will($this->returnValue(255));
		$mockEnvironment->expects($this->any())->method('getPathToTemporaryDirectory')->will($this->returnValue('vfs://Foo/'));

		$backend = $this->getMock('TYPO3\FLOW3\Cache\Backend\FileBackend', array('isCacheFileExpired'), array(), '', FALSE);
		$backend->injectEnvironment($mockEnvironment);
		$backend->setCache($mockCache);

		$backend->expects($this->once())->method('isCacheFileExpired'); // Indirectly called by freeze() -> get()

		$data = '<?php return "foo"; ?>';
		$backend->set('FooEntry', $data);

		$backend->freeze();

		$loadedData = $backend->requireOnce('FooEntry');
		$this->assertEquals('foo', $loadedData);
	}

	/**
	 * @test
	 */
	public function findIdentifiersByTagFindsCacheEntriesWithSpecifiedTag() {
		$mockCache = $this->getMock('TYPO3\FLOW3\Cache\Frontend\AbstractFrontend', array(), array(), '', FALSE);
		$mockCache->expects($this->atLeastOnce())->method('getIdentifier')->will($this->returnValue('UnitTestCache'));

		$mockEnvironment = $this->getMock('TYPO3\FLOW3\Utility\Environment', array(), array(), '', FALSE);
		$mockEnvironment->expects($this->any())->method('getMaximumPathLength')->will($this->returnValue(255));
		$mockEnvironment->expects($this->any())->method('getPathToTemporaryDirectory')->will($this->returnValue('vfs://Foo/'));

		$backend = $this->getMock('TYPO3\FLOW3\Cache\Backend\FileBackend', array('dummy'), array(), '', FALSE);
		$backend->injectEnvironment($mockEnvironment);
		$backend->setCache($mockCache);

		$data = 'some data' . microtime();
		$backend->set('BackendFileTest1', $data, array('UnitTestTag%test', 'UnitTestTag%boring'));
		$backend->set('BackendFileTest2', $data, array('UnitTestTag%test', 'UnitTestTag%special'));
		$backend->set('BackendFileTest3', $data, array('UnitTestTag%test'));

		$expectedEntry = 'BackendFileTest2';

		$actualEntries = $backend->findIdentifiersByTag('UnitTestTag%special');
		$this->assertInternalType('array', $actualEntries);

		$this->assertEquals($expectedEntry, array_pop($actualEntries));
	}

	/**
	 * @test
	 */
	public function findIdentifiersByTagReturnsEmptyArrayForExpiredEntries() {
		$mockCache = $this->getMock('TYPO3\FLOW3\Cache\Frontend\AbstractFrontend', array(), array(), '', FALSE);
		$mockCache->expects($this->atLeastOnce())->method('getIdentifier')->will($this->returnValue('UnitTestCache'));

		$mockEnvironment = $this->getMock('TYPO3\FLOW3\Utility\Environment', array(), array(), '', FALSE);
		$mockEnvironment->expects($this->any())->method('getMaximumPathLength')->will($this->returnValue(255));
		$mockEnvironment->expects($this->any())->method('getPathToTemporaryDirectory')->will($this->returnValue('vfs://Foo/'));

		$backend = $this->getMock('TYPO3\FLOW3\Cache\Backend\FileBackend', array('dummy'), array(), '', FALSE);
		$backend->injectEnvironment($mockEnvironment);
		$backend->setCache($mockCache);

		$data = 'some data';
		$backend->set('BackendFileTest1', $data, array('UnitTestTag%test', 'UnitTestTag%boring'));
		$backend->set('BackendFileTest2', $data, array('UnitTestTag%test', 'UnitTestTag%special'), -100);
		$backend->set('BackendFileTest3', $data, array('UnitTestTag%test'));

		$this->assertSame(array(), $backend->findIdentifiersByTag('UnitTestTag%special'));
		$this->assertSame(array('BackendFileTest1', 'BackendFileTest3'), $backend->findIdentifiersByTag('UnitTestTag%test'));
	}

	/**
	 * @test
	 */
	public function flushRemovesAllCacheEntries() {
		$mockCache = $this->getMock('TYPO3\FLOW3\Cache\Frontend\AbstractFrontend', array(), array(), '', FALSE);
		$mockCache->expects($this->atLeastOnce())->method('getIdentifier')->will($this->returnValue('UnitTestCache'));

		$mockEnvironment = $this->getMock('TYPO3\FLOW3\Utility\Environment', array(), array(), '', FALSE);
		$mockEnvironment->expects($this->any())->method('getMaximumPathLength')->will($this->returnValue(255));
		$mockEnvironment->expects($this->any())->method('getPathToTemporaryDirectory')->will($this->returnValue('vfs://Foo/'));

		$backend = $this->getMock('TYPO3\FLOW3\Cache\Backend\FileBackend', array('dummy'), array(), '', FALSE);
		$backend->injectEnvironment($mockEnvironment);
		$backend->setCache($mockCache);

		$data = 'some data';
		$backend->set('BackendFileTest1', $data);
		$backend->set('BackendFileTest2', $data);

		$this->assertFileExists('vfs://Foo/Cache/Data/UnitTestCache/BackendFileTest1');
		$this->assertFileExists('vfs://Foo/Cache/Data/UnitTestCache/BackendFileTest2');

		$backend->flush();

		$this->assertFileNotExists('vfs://Foo/Cache/Data/UnitTestCache/BackendFileTest1');
		$this->assertFileNotExists('vfs://Foo/Cache/Data/UnitTestCache/BackendFileTest2');
	}

	/**
	 * @test
	 */
	public function flushByTagRemovesCacheEntriesWithSpecifiedTag() {
		$backend = $this->getMock('TYPO3\FLOW3\Cache\Backend\FileBackend', array('findIdentifiersByTag', 'remove'), array(), '', FALSE);

		$backend->expects($this->once())->method('findIdentifiersByTag')->with('UnitTestTag%special')->will($this->returnValue(array('foo', 'bar', 'baz')));
		$backend->expects($this->at(1))->method('remove')->with('foo');
		$backend->expects($this->at(2))->method('remove')->with('bar');
		$backend->expects($this->at(3))->method('remove')->with('baz');

		$backend->flushByTag('UnitTestTag%special');
	}

	/**
	 * @test
	 */
	public function collectGarbageRemovesExpiredCacheEntries() {
		$mockCache = $this->getMock('TYPO3\FLOW3\Cache\Frontend\AbstractFrontend', array(), array(), '', FALSE);
		$mockCache->expects($this->atLeastOnce())->method('getIdentifier')->will($this->returnValue('UnitTestCache'));

		$mockEnvironment = $this->getMock('TYPO3\FLOW3\Utility\Environment', array(), array(), '', FALSE);
		$mockEnvironment->expects($this->any())->method('getMaximumPathLength')->will($this->returnValue(255));
		$mockEnvironment->expects($this->any())->method('getPathToTemporaryDirectory')->will($this->returnValue('vfs://Foo/'));

		$backend = $this->getMock('TYPO3\FLOW3\Cache\Backend\FileBackend', array('isCacheFileExpired'), array(), '', FALSE);
		$backend->expects($this->exactly(2))->method('isCacheFileExpired')->will($this->onConsecutiveCalls(TRUE, FALSE));
		$backend->injectEnvironment($mockEnvironment);
		$backend->setCache($mockCache);

		$data = 'some data';
		$backend->set('BackendFileTest1', $data);
		$backend->set('BackendFileTest2', $data);

		$this->assertFileExists('vfs://Foo/Cache/Data/UnitTestCache/BackendFileTest1');
		$this->assertFileExists('vfs://Foo/Cache/Data/UnitTestCache/BackendFileTest2');

		$backend->collectGarbage();
		$this->assertFileNotExists('vfs://Foo/Cache/Data/UnitTestCache/BackendFileTest1');
		$this->assertFileExists('vfs://Foo/Cache/Data/UnitTestCache/BackendFileTest2');
	}

	/**
	 * @test
	 */
	public function flushUnfreezesTheCache() {
		$mockCache = $this->getMock('TYPO3\FLOW3\Cache\Frontend\AbstractFrontend', array(), array(), '', FALSE);
		$mockCache->expects($this->atLeastOnce())->method('getIdentifier')->will($this->returnValue('UnitTestCache'));

		$mockEnvironment = $this->getMock('TYPO3\FLOW3\Utility\Environment', array(), array(), '', FALSE);
		$mockEnvironment->expects($this->any())->method('getMaximumPathLength')->will($this->returnValue(255));
		$mockEnvironment->expects($this->any())->method('getPathToTemporaryDirectory')->will($this->returnValue('vfs://Foo/'));

		$backend = $this->getMock('TYPO3\FLOW3\Cache\Backend\FileBackend', array('dummy'), array(), '', FALSE);
		$backend->injectEnvironment($mockEnvironment);
		$backend->setCache($mockCache);
		$backend->freeze();

		$this->assertTrue($backend->isFrozen());
		$backend->flush();
		$this->assertFalse($backend->isFrozen());
	}
}
?>