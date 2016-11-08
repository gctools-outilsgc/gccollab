
var fs = require('fs');
var git = require('./lib/git');
var writer = require('./lib/writer');
var extend = require('lodash.assign');

module.exports = generate;

function generate(options, done) {
  options = extend({
    version: null,
    to: 'HEAD',
    file: 'CHANGELOG.md',
    subtitle: '',
    log: console.log.bind(console),
    notes: '',
  }, options || {});

  if (!options.version) {
    return done('No version specified');
  }

  readNotes(options.notes, options.file).then(function (notes) {
    options.notes = notes;

    git.getTags(function(err, tags) {
      if (err || !tags) {
        return done('Failed to read git tags.\n'+err);
      }
      writeChangelog(tags);
    });
  });

  // consider anything before first anchor as release notes
  function readNotes(notes, file) {
    return new Promise(function (resolve, reject) {
      if (notes || !file || !fs.existsSync(file)) {
        resolve(notes);
        return;
      }

      fs.readFile(file, {encoding:'UTF-8'}, function(err, contents) {
        if (err) {
          return done('Failed to read ' + file + '.\n'+err);
        }

        var idx = contents.indexOf('<a name="');
        resolve(idx ? contents.substr(0, idx) : '');
      });
    });
  }

  function writeChangelog(tags) {
    options.exclude = options.exclude || tags;
    options.to = options.to || 'HEAD';

    options.log('Generating changelog for %s...', options.version);

    git.getLog({
      exclude: options.exclude,
      to: options.to,
    }, function(err, gitLog) {
      if (err) {
        return done('Failed to read git log.\n'+err);
      }

      writeGitLog(gitLog);
    });
  }

  function writeGitLog(gitLog) {
    options.log('Parsed %d commits.', gitLog.commits.length);
    options.log('Parsed %d contributors.', gitLog.contributors.length);

    writer.writeLog(gitLog, options, function(err, changelog) {
      if (err) {
        return done('Failed to write changelog.\n'+err);
      }

      if (options.file && fs.existsSync(options.file)) {
        fs.readFile(options.file, {encoding:'UTF-8'}, function(err, contents) {
          if (err) {
            return done('Failed to read ' + options.file + '.\n'+err);
          }

          // clear out release notes (already captured in log)
          var idx = contents.indexOf('<a name="');

          done(null, changelog + '\n' + String(contents.substring(idx)));
        });
      } else {
        done(null, changelog);
      }
    });
  }
}
