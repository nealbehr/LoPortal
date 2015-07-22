//var mozjpeg = require('imagemin-mozjpeg');

// Обязательная обёртка
module.exports = function(grunt) {
    grunt.versionFiles  = grunt.template.today("m-d-yyyy");
    grunt.nameJsMinFile = 'build/scripts.min.' + grunt.versionFiles + '.js';
    grunt.nameCSSMinFile = 'build/css.min.' + grunt.versionFiles + '.css';

    // Задачи
    grunt.initConfig({
        concat: {// Склеиваем
            options: {
                separator: ';'
                //,
                // Replace all 'use strict' statements in the code with a single one at the top
//                banner: "'use strict';\n",
//                process: function(src, filepath) {
//                    return '// Source: ' + filepath + '\n' +
//                        src.replace(/(^|\n)[ \t]*('use strict'|"use strict");?\s*/g, '$1');
//                }
            },
            main: {
                src: [
                    'web/js/modules/*.js',
                    'web/js/service/*.js',
                    'web/js/*.js'
                ],
                dest: 'web/build/scripts.js'
            }
        },
        uglify: {// Сжимаем
//            options: {
//                mangle: false
//            },
            main: {
                files: {
                    // Результат задачи concat
                    //'web/build/scripts.min.js': '<%= concat.main.dest %>'
                    'web/<%= grunt.nameJsMinFile %>': '<%= concat.main.dest %>'
                }
            }
        },
        processhtml:{
            dist: {
                files: {
                    'view/index.twig': ['view/index.twig']
                },
                options: {
                    data: {
                    }
                }
            }
        },
        'string-replace': {
            version: {
                files: {
                    'view/index.twig': ['view/index.twig']
                },
                options: {
                    replacements: [{
                        pattern: /< JS_FILENAME >/g,
                        replacement: '<%= grunt.nameJsMinFile %>'
                    },{
                        pattern: /< CSS_FILENAME >/g,
                        replacement: '<%= grunt.nameCSSMinFile %>'
                    }
                    ]
                }
            }
        },
        imagemin: {
            options: {
                optimizationLevel: 4
            },
            dynamic: {
                files: [{
                    expand: true,
                    cwd: 'web/images/',
                    src: ['**/*.{png,jpg,gif,svg}'],
                    dest: 'web/images/'
                }]
            }
        },
        cssmin: {
            options:{
                advanced: false
            },
            target: {
                files: [{
                    expand: true,
                    cwd: 'web/css',
                    src: ['*.css', '!*.min.css'],
                    dest: 'web/build/css',
                    ext: '.min.css'
                },
                {
                    'web/<%= grunt.nameCSSMinFile %>': [
                        'web/css/jquery-ui.structure.min.css',
                        'web/css/jquery-ui.min.css',
                        'web/css/bootstrap.min.css',
                        'web/css/all.min.css',
                        '<%= cssmin.target.files[0].dest %>/ng-dialog.min.css',
                        '<%= cssmin.target.files[0].dest %>/css.min.css',
                        '<%= cssmin.target.files[0].dest %>/cropper.min.css'
                    ]
                }
                ]
            }
        }
    });
    //'web/css/*.min.css',
    //'web/css/bootstrap.min.css',
    //'<%= cssmin.target.files[0].dest %>/all.min.css',
                            //src: ['*.css', '!*.min.css'],
    // Загрузка плагинов, установленных с помощью npm install
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-processhtml');
    grunt.loadNpmTasks('grunt-string-replace');
    grunt.loadNpmTasks('grunt-contrib-imagemin');
    grunt.loadNpmTasks('grunt-contrib-cssmin');

    // Задача по умолчанию
    grunt.registerTask('default', [
        'concat',
        'uglify',
        'string-replace',
        'processhtml',
//        'imagemin',
        'cssmin']
    );
};

//"grunt-contrib-imagemin": "^0.9.2",