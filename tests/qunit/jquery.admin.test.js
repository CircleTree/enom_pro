TestCase('localStorage', {
	testSupportsLocalStorage: function () {
		assertTrue('supports', enom_pro.support.localStorage());
	},
	testGetLocalStorageKey: function () {
		var got_key = enom_pro.getLocalStorageKey('key');
		assertEquals('key is wrong', 'enom_pro_key', got_key);
	},
	testSaveLocalStorage: function (){
		var set = 'bam';
		enom_pro.setLocalStorage('foobar', set);
		assertEquals('get localstorage failed', set, enom_pro.getLocalStorage('foobar'));
	}
});