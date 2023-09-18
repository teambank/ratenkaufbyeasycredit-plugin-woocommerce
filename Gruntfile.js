module.exports = function(grunt) {

    var baseDir = 'src/wc-easycredit/assets';

    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('grunt-contrib-sass');

    grunt.initConfig({
      uglify: {
        easycredit: {
          options: {
            sourceMap: true,
            sourceMapName: baseDir+'/js/easycredit.min.js.map'
          },
          files: {
            [baseDir+'/js/easycredit.min.js']: [
                baseDir+'/js/src/easycredit-frontend.js'
            ],
            [baseDir+'/js/easycredit-backend.min.js']: [
                baseDir+'/js/easycredit-backend.js'
            ]
          },
        },
      },
      cssmin: {
          options: {
            mergeIntoShorthands: false,
            roundingPrecision: -1
          },
          easycredit: {
            files: {
              [baseDir+'/css/easycredit.min.css']: [
                baseDir+'/css/src/easycredit-frontend.css'
              ],
              [baseDir+'/css/easycredit-backend.min.css'] : [
                baseDir+'/css/src/easycredit-backend.css'
              ]
            }
          }
      },
      sass: {
        dist: {
          options: {
            style: 'expanded'
          },
          files: {
            [baseDir+'/css/easycredit-backend-marketing.min.css']: [
              baseDir+'/css/src/easycredit-backend-marketing.scss'
            ]
          }
        }
      }
    });
    grunt.registerTask('default', ['uglify','cssmin','sass']);
}
