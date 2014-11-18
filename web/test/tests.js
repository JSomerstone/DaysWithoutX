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
            dateString = new Date().addMinutes(testCase.diff).toString('yyyy-M-d HH:mm:ss');

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
            dateString = new Date().addHours(testCase.diff).toString('yyyy-M-d HH:mm:ss');

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
            dateString = new Date().addDays(testCase.diff).toString('yyyy-M-d HH:mm:ss');

        assert.equal(
            dwo.convertTimestamp(dateString),
            testCase.expected
        );
    });
});
