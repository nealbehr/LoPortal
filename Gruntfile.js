//var mozjpeg = require('imagemin-mozjpeg');

// Обязательная обёртка
module.exports = function(grunt) {
    grunt.versionFiles  = grunt.template.today("m-d-yyyy");
    grunt.nameJsMinFile = 'build/scripts.min.' + grunt.versionFiles + '.js';
    grunt.nameCSSMinFile = 'build/css.min.' + grunt.versionFiles + '.css';

    // Configurable paths for the application
    var appConfig = {
        dist: 'dist'
    };

    // Tasks
    grunt.initConfig({
        // Project settings
        yeoman: appConfig,

        // Empties folders to start fresh
        clean: {
            dist: {
                files: [{
                    dot: true,
                    src: [
                        '.tmp',
                        '<%= yeoman.dist %>/{,*/}*',
                        '!<%= yeoman.dist %>/.git{,*/}*'
                    ]
                }]
            },
            server: '.tmp'
        },

        copy: {
            dist: {
                files: [{
                    expand: true,
                    dot: true,
                    cwd: '.ebextensions',
                    dest: '<%= yeoman.dist %>/.ebextensions',
                    src: '**'
                }, {
                    expand: true,
                    dot: true,
                    cwd: 'config',
                    dest: '<%= yeoman.dist %>/config',
                    src: '**'
                }, {
                    expand: true,
                    dot: true,
                    cwd: 'html',
                    dest: '<%= yeoman.dist %>/html',
                    src: '**'
                }, {
                    expand: true,
                    dot: true,
                    cwd: 'locales',
                    dest: '<%= yeoman.dist %>/locales',
                    src: '**'
                }, {
                    expand: true,
                    dest: '<%= yeoman.dist %>',
                    src: 'logs/.gitignore'
                }, {
                    expand: true,
                    dot: true,
                    cwd: 'migrations',
                    dest: '<%= yeoman.dist %>/migrations',
                    src: '**'
                }, {
                    expand: true,
                    dot: true,
                    cwd: 'puppet',
                    dest: '<%= yeoman.dist %>/puppet',
                    src: '**'
                }, {
                    expand: true,
                    dot: true,
                    cwd: 'src',
                    dest: '<%= yeoman.dist %>/src',
                    src: '**'
                }, {
                    expand: true,
                    dot: true,
                    cwd: 'tests',
                    dest: '<%= yeoman.dist %>/tests',
                    src: '**'
                }, {
                    expand: true,
                    dot: true,
                    cwd: 'view',
                    dest: '<%= yeoman.dist %>/view',
                    src: '**'
                }, {
                    expand: true,
                    dot: true,
                    cwd: 'web',
                    dest: '<%= yeoman.dist %>/web',
                    src: '**'
                }, {
                    expand: true,
                    dest: '<%= yeoman.dist %>',
                    src: '*',
                    filter: 'isFile'
                }]
            }
        },

        ebDeploy: {
            options: {
                region     : 'us-west-1',
                application: 'first-rex-lo-portal',

            },
            dev: {
                options: {
                    environment: 'firstRexLoPortal-stage',
                    profile    : 'profile eb-cli-client'
                },
                files: [
                    { src: ['.ebextensions/*'] },
                    { cwd: '<%= yeoman.dist %>/', src: ['**'], expand: true }
                ]
            },
            prod: {
                options: {
                    profile    : 'profile eb-cli-client',
                    environment: 'firstRexLoPortal'
                },
                files: [
                    { src: ['.ebextensions/*'] },
                    { cwd: '<%= yeoman.dist %>/', src: ['**'], expand: true }
                ]
            }
        },

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
                dest: '<%= yeoman.dist %>/web/build/scripts.js'
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
                    '<%= yeoman.dist %>/web/<%= grunt.nameJsMinFile %>': '<%= concat.main.dest %>'
                }
            }
        },
        processhtml:{
            dist: {
                files: {
                    '<%= yeoman.dist %>/view/index.twig': ['<%= yeoman.dist %>/view/index.twig']
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
                    '<%= yeoman.dist %>/view/index.twig': ['view/index.twig']
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
                    dest: '<%= yeoman.dist %>/web/images/'
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
                    dest: '<%= yeoman.dist %>/web/build/css',
                    ext: '.min.css'
                },
                {
                    '<%= yeoman.dist %>/web/<%= grunt.nameCSSMinFile %>': [
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
    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-eb-deploy');
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
//       'imagemin',
        'cssmin'
    ]);

    grunt.registerTask('build', [
        'clean:dist',
        'copy:dist',
        'concat',
        'uglify',
        'string-replace',
        'processhtml',
        'imagemin',
        'cssmin'
    ]);

    grunt.registerTask('deploy', [
        'build',
        'ebDeploy'
    ]);
};
