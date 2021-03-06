﻿
angular.module('authentication')
    .factory('authenticationService', function ($http, $injector) {
        'use strict';

        // Detect if an API backend is present. If so, return the API module, else
        // hand off the localStorage adapter
        return $http.get(GLOBAL_SETTINGS.fullApiPath + 'user')
            .then(function () {
                return $injector.get('authenticationApi');
            }, function () {
                return $injector.get('authenticationApi');
                //return $injector.get('authenticationLocalStorage');
            });
    })

    .factory('authenticationApi', ['$resource', '$rootScope', function ($resource, $rootScope) {
        'use strict';
        var currentUser;
        var store = {
            currentUser: currentUser,
            users: [],

            api: $resource(GLOBAL_SETTINGS.apiPath + 'login/:id', null,
                {
                    update: { method: 'PUT' }
                }
            ),

            // get: function () {
            //     return store.api.query(function (resp) {
            //         angular.copy(resp, store.users);
            //     });
            // },

            register: function (user) {
                return $resource(GLOBAL_SETTINGS.apiPath + 'user/register', null, null)
                    .save(user, function success(resp) {
                        user = resp;
                        store.currentUser = resp;
                        $rootScope.$broadcast('authenticationService:loginSuccess', store.currentUser);
                    }, function error(err) {
                        console.log('Login Error', err);
                    }).$promise;
            },

            login: function (user) {
                return $resource(GLOBAL_SETTINGS.apiPath + 'user/login', null, null)
                    .save(user, function success(resp) {
                        CONSTS.propsToLower(resp);
                        user = resp;
                        store.currentUser = resp;
                        $rootScope.$broadcast('authenticationService:loginSuccess', store.currentUser);
                    }, function error(err) {
                        console.log('Login Error', err);
                    }).$promise;
            }

        };

        return store;
    }])

    .factory('authenticationLocalStorage', function ($q) {
        'use strict';

        var STORAGE_ID = 'gift-shop-users';

        var store = {
            users: [],

            _getFromLocalStorage: function () {
                return JSON.parse(localStorage.getItem(STORAGE_ID) || '[]');
            },

            _saveToLocalStorage: function (users) {
                localStorage.setItem(STORAGE_ID, JSON.stringify(users));
            },

            _getNextId: function () {
                var users = store._getFromLocalStorage();
                var max = 0;
                for (var i = 0; i < users.length; i++) {
                    if (users[i].id > max) {
                        max = users[i].id;
                    }
                }
                return max;
            },

            get: function () {
                var deferred = $q.defer();

                angular.copy(store._getFromLocalStorage(), store.users);
                deferred.resolve(store.users);

                return deferred.promise;
            },

            insert: function (user) {
                var deferred = $q.defer();
                debugger
                user.id = store._getNextId() + 1;
                store.users.push(user);

                store._saveToLocalStorage(store.users);
                deferred.resolve(store.users);

                return deferred.promise;
            },

            login: function (user) {
                var deferred = $q.defer();
                var currentUsers = store._getFromLocalStorage();

                var registeredUser = currentUsers.filter(function (cUser) {
                    return (cUser.email === user.email && cUser.password === user.password);
                });

                if (registeredUser.length === 1) {
                    deferred.resolve(registeredUser[0]);
                } else {
                    deferred.reject('User does not exist');
                }


                return deferred.promise;
            }
        };

        return store;
    });