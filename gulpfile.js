const gulp = require('gulp');
const browserSync = require('browser-sync').create();

// Static Server + watching files
gulp.task('serve', function() {
  browserSync.init({
    proxy: 'http://localhost:2088', // Your PHP server
    port: 3000
  });

  gulp.watch('**/*.php').on('change', browserSync.reload);
});

gulp.task('default', gulp.series('serve'));
