var app = angular.module('shopApp', ['ngRoute'], function($interpolateProvider) {
	$interpolateProvider.startSymbol('[[');
	$interpolateProvider.endSymbol(']]');
});

app.config(['$routeProvider', '$locationProvider', '$httpProvider',
	function($routeProvider, $locationProvider, $httpProvider) {

		$routeProvider.when('/', {
			templateUrl: 'home-tpl.html',
			controller: 'ProductCtrl',
		});
		$routeProvider.when('/products/:id', {
			templateUrl: 'product-tpl.html',
			controller: 'SingleProductCtrl',

		});
	}
])

app.run(['$location', '$rootScope', function($location, $rootScope) {

	$rootScope.switchTo = function(path) {
		$location.path(path);
		return true;
	}

	$rootScope.util = {
		getNumber: function(num) {
			return new Array(num);
		}
	}

}])

app.controller('ProductCtrl', ['$scope', '$http', function($scope, $http) {

	$scope.searchFilter = {}

	$scope.getProducts = function() {
		$http.get(Routing.generate('api_dummy_get_products')).then(function(response) {
			$scope.products = response.data;
		})
	}

	$scope.getTags = function() {
		$http.get(Routing.generate('api_dummy_get_tags')).then(function(response) {
			$scope.tags = response.data.map(function(elem, index) {
				return {
					'name': elem,
					'selected': !index
				}
			});
		})
	}

	$scope.toggleTag = function(tag) {

		$scope.tags.forEach(function(_tag) {
			_tag.selected = false;
		})

		tag.selected = true;

		$scope.searchFilter.tag = $scope.tags[0].name == tag.name ? '' : tag.name;
	}

	$scope.getProducts();
	$scope.getTags();

}])

app.controller('SingleProductCtrl', ['$scope', '$routeParams', 'ProductService', function($scope, $routeParams, ProductService) {

	$scope.ProductService = ProductService;

	ProductService.getSingleProduct($routeParams.id).then(function(product) {
		$scope.selectedProduct = product;
	});

}])

app.service('ProductService', ['$http', '$q', function($http, $q) {

	var self = this;

	self.productCache = {};

	self.hasProduct = function(id) {
		return self.productCache[id] !== undefined && self.productCache[id].id;
	}

	self.getSingleProduct = function(id) {

		var deferred = $q.defer();

		if (self.hasProduct(id)) {
			deferred.resolve(self.productCache[id]);
		} else {
			$http.get(Routing.generate('api_dummy_get_product', {
				id: id
			})).then(function(response) {

				if (response.status !== 200){
					deferred.reject({"reason" : "whatever happened to your server"});
				}

				var product = response.data;

				deferred.resolve(response.data);

				self.productCache[product.id] = response.data;
			})
		}
		return deferred.promise;
	}

	return self;

}])

app.directive('ngPreload', ['$timeout', 'ProductService', function($timeout, ProductService) {
	return {
		restrict: 'A',
		link: function(scope, iElement, iAttrs) {

			var promise = null;

			iElement.on('mouseenter', function() {

				promise = $timeout(function() {

					ProductService.getSingleProduct(scope.product.id);

				}, 2000)

			})

			iElement.on('mouseleave', function() {

				$timeout.cancel(promise);

			})

		}
	};
}])

var countWatchers = function () { 
    var root = angular.element(document.getElementsByTagName('body'));

    var watchers = [];

    var f = function (element) {
        angular.forEach(['$scope', '$isolateScope'], function (scopeProperty) { 
            if (element.data() && element.data().hasOwnProperty(scopeProperty)) {
                angular.forEach(element.data()[scopeProperty].$$watchers, function (watcher) {
                    watchers.push(watcher);
                });
            }
        });

        angular.forEach(element.children(), function (childElement) {
            f(angular.element(childElement));
        });
    };

    f(root);

    // Remove duplicate watchers
    var watchersWithoutDuplicates = [];
    angular.forEach(watchers, function(item) {
        if(watchersWithoutDuplicates.indexOf(item) < 0) {
             watchersWithoutDuplicates.push(item);
        }
    });

    console.log(watchersWithoutDuplicates.length);
};