var preLoaded = [];
var bankBalance = 0;
var lastWeather = 0;
var liveUpdates = true;
var sidebar = $("nav#menu");

function createAlert(options) {
	$('#menu .panel p').text(options['message']);
	$('#menu .panel').fadeIn();
}

// Program a interval to update the weather every 5 minutes after opening the page
function getWeather(data, useGeoLocation) {
	var options = {
		useEasing : true,
		useGrouping : true,
		suffix : '°F'
	};

	if (useGeoLocation) {
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
			swal({
				title: 'Whoops!',
				text: 'We need permission to use geolocation to update the weather!',
				type: 'error'
			});
		}
	}
	else {

		if (data.response.responseSuccess != true) {
			// error loading weather
			console.error('Error fetching weather from hydrate endpoint')
			return;
		}

		var temp = Math.round(data.response.weather.temp);
		var weatherCounter = new CountUp('weather', lastWeather, temp, 0, 2.5, options);
		weatherCounter.start();
		lastWeather = temp;
	}
}

function getWeatherData() {
	$.ajax({
		url: '/dashboard/hydrate/',
		type: 'GET',
		data: {
			'action': 'weather'
		},
		dataType: 'json',
		cache: false,
		success: function(data) {
			getWeather(data, false);
		},
		error: function(request, status, error) {}
	});
}

function getUploadsData() {
	$.ajax({
		url: '/dashboard/hydrate/',
		type: 'GET',
		data: {
			'action': 'uploads'
		},
		dataType: 'json',
		cache: false,
		success: function(data) {
			generateFileTimeline(data);
		},
		error: function(request, status, error) {}
	});
}

function getUploadKeys() {
	$.ajax({
		url: '/dashboard/hydrate/',
		type: 'GET',
		data: {
			'action': 'keys'
		},
		dataType: 'json',
		cache: false,
		success: function(data) {
			getKeys(data);
		},
		error: function(request, status, error) {}
	});
}

// Get data from our private API
function getData(reload, id) {
	$.ajax({
		url: '/ajax/api/',
		type: 'GET',
		data: {id: id},
		dataType: 'json',
		cache: false,
		success: function(data) {
			console.log(data);
			getPosts(data);
		},
		error: function(request, status, error) {}
	});
}

// List view all active keys
function getKeys(data) {
	if (data.upload_keys != null) {
		var keys = data.upload_keys.length;
		$('[data-load=keys').text(keys);
	}
}

function stopDefaultEvents(event) {
	event.stopPropagation();
	event.preventDefault();
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

function generateFileTimeline(data) {
	var $timeline = $('#timeline');
	if ($timeline.children().length > 0){
		$timeline.empty();
	}

	if (data.response.responseSuccess && data.response.uploads != null) {
		for (var i = data.response.uploads.length - 1; i >= 0; i--) {
			var upload = data.response.uploads[i];
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
	getData(true, userId);
	getWeatherData();
	getUploadsData();
	getUploadKeys();

	// Check for page changes
	getPageAdjustments();
	$(window).on('resize', function() {
		getPageAdjustments();
	});

	$('#uploadForm').submit(function(event) {

		// stop
		stopDefaultEvents(event);

		// request
		$.ajax({
			type: $(this).attr('method'),
			url: $(this).attr('action'),
			clearForm: true,
			cache: false,
			data: new FormData($('#uploadForm')[0]),
			processData: false,
			contentType: false,
			success: function(data) {
				console.log(data);
				if (data.response !== 0) {
					swal({
						title: 'File Uploaded!',
						text: data.message,
						type: 'success'
					});
					generateFileTimeline();
				}
				else {
					swal({
						title: 'Unexpected Error',
						text: data.message,
						type: 'error'
					});
				}
			},
			error: function(request, status, error) {
				console.log(request);
				console.log(status);
				console.log(error);
				swal({
					title: 'Error',
					text: 'Something weird happened while trying to upload this file',
					type: 'error'
				});
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

	// Load list of people
	$('a').click(function() {
		if($(this).attr("data-action")) {
			$action = $(this).attr("data-action");
			if ($action == 'load-people') {
				swal({
					title: 'Unexpected Error',
					text: 'Something weird happened while loading the list of people',
					type: 'error'
				});
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
					getData(true, userId);
				});
			}
		}
	});
})
