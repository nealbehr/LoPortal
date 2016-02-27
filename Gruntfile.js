'use strict';

/**
 * Create build and deploy on amazon elastic beanstalk
 */
module.exports = function(grunt) {
    grunt.versionFiles          = new Date().getTime();
    grunt.nameJsMinFile         = 'build/scripts-'+grunt.versionFiles+'.min.js';
    grunt.nameCSSMinFile        = 'build/css-'+grunt.versionFiles+'.min.css';
    grunt.nameTemplateCacheFile = 'build/template-cache-'+grunt.versionFiles+'.js';

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
            },
            distFolder: {
                files: [{
                    dot: true,
                    src: [
                        '<%= yeoman.dist %>'
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
                    cwd: 'web/fonts',
                    dest: '<%= yeoman.dist %>/web/build/fonts',
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
        // Cached templates
        ngtemplates:  {
            options: {
                module : 'loApp',
                htmlmin:  {
                    collapseWhitespace       : true,
                    collapseBooleanAttributes: true }
            },
            app: {
                cwd:  'web',
                src:  'template/**/**.html',
                dest: '<%= yeoman.dist %>/web/<%= grunt.nameTemplateCacheFile %>'
            }
        },
        // Concat js files
        concat: {
            options: {
                separator: ';'
            },
            main: {
                src: [
                    'web/js/lib/*.js',
                    'web/js/*.js',
                    'web/js/modules/*.js',
                    'web/js/service/*.js',
                    'web/js/directive/*.js'
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
                    }, {
                        pattern: /< JS_TEMPLATE_CACHE >/g,
                        replacement: '<%= grunt.nameTemplateCacheFile %>'
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
            options: {
                advanced: false
            },
            target: {
                files: [{
                    expand: true,
                    cwd: 'web/css',
                    src: ['*.css', '!*.min.css'],
                    dest: '<%= yeoman.dist %>/web/build/css',
                    ext: '.min.css'
                }, {
                    '<%= yeoman.dist %>/web/<%= grunt.nameCSSMinFile %>': [
                        'web/css/jquery-ui.structure.min.css',
                        'web/css/jquery-ui.min.css',
                        'web/css/bootstrap.min.css',
                        'web/css/font-awesome.min.css',
                        'web/css/all.min.css',
                        '<%= cssmin.target.files[0].dest %>/ng-dialog.min.css',
                        '<%= cssmin.target.files[0].dest %>/css.min.css',
                        '<%= cssmin.target.files[0].dest %>/cropper.min.css'
                    ]
                }]
            }
        },
        // Deploy on elastic beanstalk
        ebDeploy: {
            options: {
                region: 'us-west-1'
            },
            stage: {
                options: {
                    profile    : 'eb-client',
                    application: 'portal-1rex-com',
                    environment: 'portal1rexcom-stage'
                },
                files: [
                    { src: ['.ebextensions/*'] },
                    { cwd: '<%= yeoman.dist %>/', src: ['**'], expand: true},
                    { cwd: '<%= yeoman.dist %>/web/', src: ['.*'], expand: true, dest: 'web/' }
                ]
            },
            prod: {
                options: {
                    profile    : 'eb-client',
                    application: 'first-rex-lo-portal',
                    environment: 'firstRexLoPortal'
                },
                files: [
                    { src: ['.ebextensions/*'] },
                    { cwd: '<%= yeoman.dist %>/', src: ['**'], expand: true },
                    { cwd: '<%= yeoman.dist %>/web/', src: ['.*'], expand: true, dest: 'web/' }
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
    grunt.loadNpmTasks('grunt-angular-templates');

    // Register tasks
    grunt.registerTask('build', [
        'clean:before',
        'copy:dist',
        'ngtemplates',
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
        'ebDeploy:stage',
        'clean:distFolder'
    ]);

    grunt.registerTask('deploy-prod', [
        'build',
        'ebDeploy:prod',
        'clean:distFolder'
    ]);
};
