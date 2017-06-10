const FILE_UPLOAD_PATH = "/ajax/upload/";

var preLoaded = [];
var bankBalance = 0;
var lastWeather = 0;
var liveUpdates = true;
var sidebar = $("nav#menu");

var comments = [
	"Please let me know if you have any other questions or concerns.",
	"Please advise if you have any additional questions regarding this.",
	"Please let me know if you have any other questions.",
	"Please let me know if I can be of further assistance.",
	"Please let me know if I am able to assist you further."
];

// --- Constants --- //
const spotifyKey = 'BQDC8z1CtWrJkyatxjDszC2RcjOkYD_1AgMvjNJZ4rcQsg2hPFc_hjI6GqygtSjVgpbbpgLR2hVWa69crdJJi3PyYs63BUGIT3oU3E3ia11CxuOaTsMHWJqik-j8rHxxcMmG6eZhYcsNlm2U2QhEvwsp6nRuLAooY2LKF9_K1TmyfA';


function createAlert(options) {
	$('#menu .panel p').text(options['message']);
	$('#menu .panel').fadeIn();
}

// Program a interval to update the weather every 5 minutes after opening the page
function getWeather(data, far) {
	var options = {
		useEasing : true,
		useGrouping : true,
		suffix : '°F'
	};

	if (far) {
		if (navigator.geolocation) {
			navigator.geolocation.getCurrentPosition(function(position) {
				var pos = {
					lat: position.coords.latitude,
					lon: position.coords.longitude
				};

				var additives = '&mode=json&type=accurate&units=imperial';
				var link = '//api.openweathermap.org/data/2.5/weather?lat=' + pos.lat + '&lon=' + pos.lon + additives + '&appid=' + weatherKey;

				$.ajax({
					url: link,
					type: 'GET',
					dataType: 'json',
					success: function(data) {
						//var temp = Math.round(data.main.temp);
						var temp = 20;
						console.log('Today\'s temperature: ' + temp);

						var weather = new CountUp('weather', lastWeather, temp, 0, 2.5, options);
						weather.start();
						lastWeather = temp;
					},
					error: function() {
						var options = {
							message: 'An unexpected problem occurred while updating the weather..'
						}
						createAlert(options);
					}
				});
			}, function() {
			});
		}
		else {
			swal('An error ocoured', 'We need permission to use geolocation to update the weather!', 'error');
		}
	}
	else {
		//var temp = Math.round(data.weather.main.temp);
		var temp = 20;
		var weather = new CountUp('weather', lastWeather, temp, 0, 2.5, options);
		weather.start();
		lastWeather = temp;
	}
}

// Get data from our private API
function getData(reload) {
	$.ajax({
		url: '/ajax/api/' + userId,
		type: 'GET',
		dataType: 'json',
		cache: false,
		success: function(data) {
			console.log(data);
			getWeather(data, false);
			getPosts(data);
			getFileTimeline(data);
			getKeys(data);
		},
		error: function(request, status, error) {}
	});
}

// List view all active keys
function getKeys(data) {
	var keys = data.upload_keys.length;
	$('[data-load=keys').text(keys);
}

function getPosts(data) {
	var posts = $('#posts');

	if (posts.children().length > 0){
		posts.empty();
		posts.masonry();
	}

	if (data.response != 0 && data.posts != null) {
		for (var i = data.posts.length - 1; i >= 0; i--) {
			var post = data.posts[i];
			preLoaded.push(post.id);
			if (post.hide == 1) continue;
			posts.append('<div class="post ' + post.id + '" >' +
				'<div class="text-content">' + post.content + '</div>' +
				'<div class="bottom-content">' + $.timeago(new Date(post.created_at)) + '..' + '</div>' +
				'</div>'
			);
		}

		// Scatter
		posts.masonry({
			itemSelector: '.post',
			columnWidth: 300,
			gutter: 30
		});
	}
}

function getPageAdjustments() {
	var wSize = $(window).width();
	if (wSize <= 780) {
		sidebar.hide();
	}
}

function getFileTimeline(data) {
	var $timeline = $('#timeline');
	if ($timeline.children().length > 0){
		$timeline.empty();
	}

	if (data.response != 0 && data.uploads != null) {
		for (var i = data.uploads.length - 1; i >= 0; i--) {
			var upload = data.uploads[i];
			$timeline.append(
				'<div class="fileBox ' + upload.id + '" >' +
				'<a href="/up/' + upload.hash + '.' + upload.extension + '">' + upload.hash + '.' + upload.extension + '</a>' +
				'</div>'
			);
		}
	}
}

function getCleanTime() {
	var date = new Date();
	var meridiem = date.getHours() > 12 ? "PM" : "AM";
	var hours = date.getHours() > 12 ? date.getHours() - 12 : date.getHours();
	var cleanTime = date.getMonth() + "/" + date.getMonth() + "/" + date.getYear() + " " + hours + ":" + date.getMinutes() + " " + meridiem +"):";
	return cleanTime;
}

$(document).ready(function() {
	if (!Notification) {
		return;
	}

	if (Notification.permission !== "granted") {
		Notification.requestPermission();
	}

	var clipboard = new Clipboard('.gen');

	// Load API data
	getData(true);

	// Check for page changes
	getPageAdjustments();
	$(window).on('resize', function() {
		getPageAdjustments();
	});

	// Update weather every 5 minutes
	// This is an issue, find new meathod of updating content
	setInterval(function() {
		var notification = new Notification('Time for some water', {
			icon: 'http://cdn.sstatic.net/stackexchange/img/logos/so/so-icon.png',
			body: "Hey! Drink 1/4ths of water!",
		});
	}, (60*1000)*5);

	setInterval(function() {
		var notification = new Notification('Time to get fresh water', {
			icon: 'http://cdn.sstatic.net/stackexchange/img/logos/so/so-icon.png',
			body: "Hey! Time to get some new water for that bottle!",
		});
	}, (60*1000)*20);

	setInterval(function() {
		var notification = new Notification('Fix your posture', {
			icon: 'http://cdn.sstatic.net/stackexchange/img/logos/so/so-icon.png',
			body: "Hey! Straighten up your back and neck!",
		});
	}, (60*1000)*3);

	$('#uploadForm').submit(function(event) {
		event.stopPropagation();
		event.preventDefault();

		$.ajax({
			type: 'POST',
			url: FILE_UPLOAD_PATH,
			clearForm: true,
			cache: false,
			data: new FormData($('#uploadForm')[0]),
			processData: false,
			contentType: false,
			success: function(data) {
				console.log(data);
				if (data.response !== 0) {
					swal('File uploaded', data.message, 'success');
					getFileTimeline(); // Needs data variable
				}
				else {
					swal('Error', data.message, 'error');
				}
			},
			error: function(request, status, error) {
				console.log(request);
				console.log(status);
				console.log(error);
				swal('Error', 'Something weird happened while trying to upload this file', 'error');
			}
		});

		return false;
	});

	// Toggling posts
	$('input:checkbox').change(function(){
		var checked = $(this).is(':checked');
		liveUpdates = checked;
	});

	// Load list of people
	$('a.gen').click(function() {
		var choice = Math.floor(Math.random()*comments.length);
		$("#generated-comment").text(comments[choice]);
	});


	var error = "Unexpected Error";
	// Load list of people
	$('a').click(function() {
		if($(this).attr("data-action")) {
			$action = $(this).attr("data-action");
			if ($action == 'load-people') {
				swal(error, 'Something weird happened while loading the list of people', 'error');
			}
			else if ($action == 'update-view') {
				swal({
					title: 'Update view',
					text: 'Using this action will update everything on screen',
					type: "info",
					showCancelButton: true,
					closeOnConfirm: true,
					showLoaderOnConfirm: true,
				},
				function(input) {
					console.log(getCleanTime());
					getData(true);
				});
			}
		}
	});

	console.log(getCleanTime());
})
