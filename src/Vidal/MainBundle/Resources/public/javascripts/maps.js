var map;
var regionId;

ymaps.ready(function() {
	regionId = $('.select').val();

	if (supports_html5_storage()) {
		var coordsData = localStorage.getItem("coordsData") || false;
		if (coordsData) {
			init(JSON.parse(coordsData), true);
		}
		else {
			$.getJSON('/pharmacies-objects/' + regionId, function(data) {
				init(data, false);
			});
		}
	}
	else {
		$.getJSON('/pharmacies-objects/' + regionId, function(data) {
			init(data, false);
		});
	}
});

function init(data, isFull) {
	$('#map_loader').hide();

	map = new ymaps.Map('map', {
		center: [data.region.latitude, data.region.longitude],
		zoom:   data.region.zoom
	});

	var objectManager = new ymaps.ObjectManager({
		// Чтобы метки начали кластеризоваться, выставляем опцию.
		clusterize: true,
		// ObjectManager принимает те же опции, что и кластеризатор.
		gridSize:   64
	});

	// Чтобы задать опции одиночным объектам и кластерам, обратимся к дочерним коллекциям ObjectManager.

	objectManager.clusters.options.set('preset', 'islands#redClusterIcons');
	objectManager.objects.options.set('iconLayout', 'default#image');
	objectManager.objects.options.set('iconImageHref', '/bundles/vidalmain/images/apt.png');
	objectManager.objects.options.set('iconImageSize', [30, 30]);
	objectManager.objects.options.set('iconImageOffset', [-20, -7]);

	// обработчик открытия метки
	objectManager.objects.events.add('click', function(e) {
		var objectId = e.get('objectId');
		var obj = objectManager.objects.getById(objectId);

		// если содержимое балуна пустое - заполняем
		if (obj.properties.balloonContent.length == 0) {
			obj.properties.balloonContent = 'Идет загрузка данных...';
			objectManager.objects.balloon.open(objectId);

			$.getJSON('/getMapBalloonContent/' +objectId, function(balloonHtml) {
				if (balloonHtml.length) {
					obj.properties.balloonContent = balloonHtml;
					objectManager.objects.balloon.open(objectId);
				}
				else {
					// если не нашли содержимое для балуна - заполняем его адресом по координатам
					var coords = obj.geometry.coordinates;
					var myGeocoder = ymaps.geocode(coords);

					myGeocoder.then(
						function (res) {
							var object = res.geoObjects.get(0);
							obj.properties.balloonContent = object.properties.get('balloonContent');
							objectManager.objects.balloon.open(objectId);
						},
						function (err) {
							obj.properties.balloonContent = 'Нет данных';
							objectManager.objects.balloon.open(objectId);
						}
					);
				}
			});
		}
	});

	map.geoObjects.add(objectManager);
	objectManager.add(data.coords);

	if (!isFull) {
		$.getJSON('/pharmacies-objects/' + regionId + '/1', function(data) {
			objectManager.add(data.coords);

			if (supports_html5_storage()) {
				localStorage.setItem('coordsData', JSON.stringify(data));
			}
		});
	}
}

function supports_html5_storage() {
	try {
		return 'localStorage' in window && window['localStorage'] !== null;
	} catch (e) {
		return false;
	}
}

$(document).ready(function() {
	$('.select')
		.chosen({
			disable_search:  true,
			no_results_text: "не найдено"
		})
		.change(function() {
			var regionId = $('.select').val();
			$.getJSON('/pharmacies-region/' + regionId, function(region) {
				var coords = [parseFloat(region.latitude), parseFloat(region.longitude)];
				map.panTo(coords, {flying: false});
				map.setCenter(coords, region.zoom);
			});
		});
});