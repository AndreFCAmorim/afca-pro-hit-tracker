(function () {
	if (typeof phtData === 'undefined') return;

	var sessionKey = 'pht_tracked_' + phtData.postId;
	if (sessionStorage.getItem(sessionKey)) return;

	fetch(phtData.nonceUrl, { credentials: 'same-origin' })
		.then(function (res) { return res.json(); })
		.then(function (data) {
			if (!data.nonce) throw new Error('no nonce');
			return fetch(phtData.restUrl, {
				method: 'POST',
				credentials: 'same-origin',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': data.nonce,
				},
				body: JSON.stringify({ post_id: phtData.postId, hp: '' }),
			});
		})
		.then(function (res) {
			if (res.ok) sessionStorage.setItem(sessionKey, '1');
		})
		.catch(function () { });
}());