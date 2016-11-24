'use strict';
module.exports = function(grunt) {
	grunt.config.set('watch', {
		scripts: {
			files: ['**/*.js'],
			tasks: [],
			options: {
				spawn: false,
			},
		},
	});
};