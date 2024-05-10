module.exports = function(){

	function convertTimeToTimeZone(isoDateTime, timeZone) {
		const date = new Date(isoDateTime);
		const options = {
			timeZone: timeZone,
			year: 'numeric',
			month: '2-digit',
			day: '2-digit',
			hour: '2-digit',
			minute: '2-digit',
			second: '2-digit',
			hour12: false,
		};
		return date.toLocaleTimeString('ja-JP', options);
	}

	this.drawWorldClock = function(isoDateTime, $targetElm){
		$target = $($targetElm);

		var timezones = {
			"Pacific/Honolulu": "Honolulu",
			"America/Adak": "America/Adak",
			"America/Los_Angeles": "Los Angeles",
			"America/New_York": "New York",
			"Europe/London": "London",
			"Europe/Berlin": "Berlin",
			"Europe/Moscow": "Moscow",
			"Asia/Kolkata": "Kolkata",
			"Asia/Singapore": "Singapore",
			"Asia/Tokyo": "Tokyo",
			"Pacific/Auckland": "New Zealand Auckland",
		};
		var src = '';
		src += '<div class="os-world-clock">';
		src += '<table>';
		Object.keys(timezones).forEach((timezone)=>{
			src += `<tr>`;
			src += `<td style="text-align: right;">${timezones[timezone]}:</td>`;
			src += `<td style="text-align: left;">${convertTimeToTimeZone(isoDateTime, timezone)}</td>`;
			src += `</tr>`;
		});
		src += '</table>';
		src += '</div>';
		$target.html(src);
	}
};