var CACHE_KEY = 'sheetData';
var CACHE_EXPIRY = 300; // 5 minutes
var SPREADSHEET_ID = '1tsoSpYKopOd48Z87CDWqJ7955LIcP8HTN9tZNu8o0Kw';

function doGet(e) {
  try {
    var data = getData();

    // Return JSON with CORS headers so any website can fetch it
    var output = ContentService.createTextOutput(JSON.stringify({
      success: true,
      data: data
    }));
    output.setMimeType(ContentService.MimeType.JSON);
    return output;

  } catch (err) {
    var output = ContentService.createTextOutput(JSON.stringify({
      success: false,
      error: err.message
    }));
    output.setMimeType(ContentService.MimeType.JSON);
    return output;
  }
}

function getData() {
  try {
    var cache = CacheService.getScriptCache();
    var cached = cache.get(CACHE_KEY);

    if (cached) {
      return JSON.parse(cached);
    }

    var sheet = SpreadsheetApp.openById(SPREADSHEET_ID).getSheetByName('Sheet1');
    var data = sheet.getDataRange().getValues();

    // Clean phone numbers (remove leading '=')
    data = data.map(function(row) {
      if (typeof row[2] === 'string' && row[2].startsWith('=')) {
        row[2] = row[2].substring(1);
      }
      return row;
    });

    try {
      cache.put(CACHE_KEY, JSON.stringify(data), CACHE_EXPIRY);
    } catch (cacheError) {
      console.log('Cache put failed: ' + cacheError.message);
    }

    return data;

  } catch (e) {
    console.error('getData error: ' + e.message);
    throw new Error('Failed to load data: ' + e.message);
  }
}

function invalidateCache() {
  CacheService.getScriptCache().remove(CACHE_KEY);
}