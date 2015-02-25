QUnit.test( "Date conversion - minutes", function( assert )
{
    var testCases = [
        { diff: -1, expected: 'just now'},
        { diff: -2, expected: '2 minutes ago'},
        { diff: -27, expected: '27 minutes ago'},
        { diff: -59, expected: '59 minutes ago'}
    ];

    jQuery.each(testCases, function(i)
    {
        var testCase = testCases[i],
            dateString = new Date().addMinutes(testCase.diff).toISOString();

        assert.equal(
            dwo.convertTimestamp(dateString),
            testCase.expected
        );
    });
});

QUnit.test( "Date conversion - seconds", function( assert )
{
    var testCases = [
        { diff: -1, expected: 'just now'},
        { diff: -2, expected: 'just now'},
        { diff: -27, expected: 'just now'},
        { diff: -60, expected: 'just now'},
        { diff: -119, expected: 'just now'},
        { diff: -120, expected: '2 minutes ago'}
    ];

    jQuery.each(testCases, function(i)
    {
        var testCase = testCases[i],
            dateString = new Date().addSeconds(testCase.diff).toISOString();

        assert.equal(
            dwo.convertTimestamp(dateString),
            testCase.expected
        );
    });
});

QUnit.test( "Date conversion - hours", function( assert )
{
    var testCases = [
        { diff: -1, expected: 'hour ago'},
        { diff: -2, expected: '2 hours ago'},
        { diff: -23, expected: '23 hours ago'},
        { diff: -24, expected: 'yesterday'},
        { diff: -48, expected: '2 days ago'}
    ];

    jQuery.each(testCases, function(i)
    {
        var testCase = testCases[i],
            dateString = new Date().addHours(testCase.diff).toISOString();

        assert.equal(
            dwo.convertTimestamp(dateString),
            testCase.expected
        );
    });
});

QUnit.test( "Date conversion - days", function( assert )
{
    var testCases = [
        { diff: -1, expected: 'yesterday'},
        { diff: -2, expected: '2 days ago'},
        { diff: -6, expected: '6 days ago'},
        { diff: -7, expected: 'last week'},
        { diff: -13, expected: 'last week'},
        { diff: -14, expected: '2 weeks ago'},
        { diff: -31, expected: 'last month'},
        { diff: -60, expected: '2 months ago'}
    ];

    jQuery.each(testCases, function(i)
    {
        var testCase = testCases[i],
            dateString = new Date().addDays(testCase.diff).toISOString();

        assert.equal(
            dwo.convertTimestamp(dateString),
            testCase.expected
        );
    });
});

QUnit.test( "Date conversion - months", function( assert )
{
    var testCases = [
        { diff: -1, expected: 'last month'},
        { diff: -2, expected: '2 months ago'},
        { diff: -6, expected: '6 months ago'},
        { diff: -11, expected: '11 months ago'},
        { diff: -12, expected: 'last year'},
        { diff: -23, expected: 'last year'},
        { diff: -24, expected: '2 years ago'}
    ];

    jQuery.each(testCases, function(i)
    {
        var testCase = testCases[i],
            dateString = new Date().addMonths(testCase.diff).toString('yyyy-M-d HH:mm:ss');

        assert.equal(
            dwo.convertTimestamp(dateString),
            testCase.expected
        );
    });
});

QUnit.test( "Date conversion - years", function( assert )
{
    var testCases = [
        { diff: -1, expected: 'last year'},
        { diff: -2, expected: '2 years ago'},
        { diff: -3, expected: '3 years ago'},
        { diff: -5, expected: '5 years ago'},
        { diff: -8, expected: '8 years ago'},
        { diff: -13, expected: '13 years ago'}
    ];

    jQuery.each(testCases, function(i)
    {
        var testCase = testCases[i],
            dateString = new Date().addYears(testCase.diff).toString('yyyy-M-d HH:mm:ss');

        assert.equal(
            dwo.convertTimestamp(dateString),
            testCase.expected
        );
    });
});

QUnit.test( "Url formation", function( assert )
{
    assert.equal(
        dwo.formApiUrl('counter/delete', 'NameOfCounter', 'NameOfUser'),
        '/api/counter/delete/NameOfCounter/NameOfUser'
    );

    assert.equal(
        dwo.formApiUrl('counter/delete', 'NameOfCounter'),
        '/api/counter/delete/NameOfCounter'
    );
});

QUnit.test( "Reset-url formation", function( assert )
{
    assert.equal(
        dwo.formApiUrl('counter/reset', 'NameOfCounter', 'NameOfUser'),
        '/api/counter/reset/NameOfCounter/NameOfUser'
    );

    assert.equal(
        dwo.formApiUrl('counter/reset', 'NameOfCounter'),
        '/api/counter/reset/NameOfCounter'
    );
});
