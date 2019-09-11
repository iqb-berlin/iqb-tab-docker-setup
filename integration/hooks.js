const hooks = require('hooks');
const fs = require("fs");

const stash = {};

hooks.beforeEachValidation(function(transaction) {

    // don't compare headers
    transaction.real.headers = {};
    transaction.expected.headers = {};
});

hooks.beforeEach(function(transaction, done) {

    // dont' check error responses
    // if (transaction.expected.statusCode.substr(0,1) !== "2") {
    //     transaction.skip = true;
    // }

    // use login credentials
    if (typeof transaction.request.headers['AuthToken'] !== "undefined") {
        let authToken = JSON.parse(transaction.request.headers['AuthToken']);
        authToken.at = stash.authToken;
        hooks.log("restoring auth token:" + stash.authToken );
        transaction.request.headers['AuthToken'] = JSON.stringify(authToken);
        transaction.request.headers['restored'] = 'yessss:' + stash.authToken;
    }
    transaction.request.headers['Accept'] = "*/*";
    done();
});

hooks.after('Login > Login', function(transaction, done) {

    // store login credentials
    stash.authToken = JSON.parse(transaction.real.body).admintoken;
    hooks.log("stashing auth token:" + stash.authToken );
    done();
});

hooks.before('get file > get file', function(transaction, done) {

    const atParameterRegex = /at=[\w\\.]+/gm;
    transaction.fullPath = transaction.fullPath.replace(atParameterRegex, 'at=' + stash.authToken);
    transaction.expected.body = fs.readFileSync('../vo_data/ws_1/Unit/SAMPLE_UNIT.XML', 'utf-8').toString();
    done();
});

hooks.before('/php/getFile.php > get file > 200 > application/octet-stream', function(transaction, done) {

    transaction.skip = true;
    done();
});
