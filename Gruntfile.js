'use strict';

/**
 * Create build and deploy on amazon elastic beanstalk
 */
module.exports = function(grunt) {
    grunt.versionFiles   = grunt.template.today('m-d-yyyy');
    grunt.nameJsMinFile  = 'build/scripts.min.'+grunt.versionFiles+'.js';
    grunt.nameCSSMinFile = 'build/css.min.'+grunt.versionFiles+'.css';

    // Tasks
    grunt.initConfig({
        // Project settings
        yeoman: {
            dist: 'dist'
        },
        clean: {
            before: {
                files: [{
                    dot: true,
                    src: [
                        '.tmp',
                        '<%= yeoman.dist %>/{,*/}*',
                        '!<%= yeoman.dist %>/.git{,*/}*'
                    ]
                }]
            },
            after: {
                files: [{
                    dot: true,
                    src: [
                        '<%= yeoman.dist %>/config/config.yml',
                        '<%= yeoman.dist %>/web/.DS_Store'
                    ]
                }]
            }
        },
        // Copy the directories for prepare to deploy
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
                    dot: true,
                    cwd: 'migrations',
                    dest: '<%= yeoman.dist %>/migrations',
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
                    src: 'web/.htaccess'
                }, {
                    expand: true,
                    dest: '<%= yeoman.dist %>',
                    src: '*',
                    filter: 'isFile'
                }]
            }
        },
        // Deploy on elastic beanstalk
        ebDeploy: {
            options: {
                region     : 'us-west-1',
                application: 'first-rex-portal'

            },
            dev: {
                options: {
                    profile    : 'eb-client-stage',
                    environment: 'firstRexPortal-stage'
                },
                files: [
                    { src: ['.ebextensions/*'] },
                    { cwd: '<%= yeoman.dist %>/', src: ['**'], expand: true},
                    { cwd: '<%= yeoman.dist %>/web/', src: ['.*'], expand: true, dest: 'web/' }
                ]
            },
            prod: {
                options: {
                    profile    : 'eb-cli',
                    environment: 'firstRexLoPortal'
                },
                files: [
                    { src: ['.ebextensions/*'] },
                    { cwd: '<%= yeoman.dist %>/', src: ['**'], expand: true },
                    { cwd: '<%= yeoman.dist %>/web/', src: ['.*'], expand: true, dest: 'web/' }
                ]
            }
        },
        concat: {
            options: {
                separator: ';'
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
        uglify: {
            main: {
                files: {
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
                    }, {
                        pattern: /< CSS_FILENAME >/g,
                        replacement: '<%= grunt.nameCSSMinFile %>'
                    }]
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

    // Loading grunt plugins
    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-eb-deploy');
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-processhtml');
    grunt.loadNpmTasks('grunt-string-replace');
    grunt.loadNpmTasks('grunt-contrib-imagemin');
    grunt.loadNpmTasks('grunt-contrib-cssmin');

    // Register tasks
    grunt.registerTask('build', [
        'clean:before',
        'copy:dist',
        'concat',
        'uglify',
        'string-replace',
        'processhtml',
        'imagemin',
        'cssmin',
        'clean:after'
    ]);

    grunt.registerTask('default', [
        'build'
    ]);

    grunt.registerTask('deploy-stage', [
        'build',
        'ebDeploy:dev'
    ]);

    grunt.registerTask('deploy-prod', [
        'build',
        'ebDeploy:prod'
    ]);
};
