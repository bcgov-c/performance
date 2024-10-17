async function main() {
  let browser;
  const fileDir = './temp/lighthouse/';

  async function importModule(moduleName) {
    const module = await import(moduleName);
    return module;
  }

  const dotenvModule = await importModule('dotenv');
  const dotenv = dotenvModule.default || dotenvModule;
  dotenv.config();

  // Setup test variables from .env file
  const testURL = process.env.APP_HOST_URL + '/login';
  const username = process.env.TESTER_USERNAME;
  const password = process.env.TESTER_PASSWORD;

  if (!testURL) {
    throw new Error(`APP_HOST_URL is not defined`);
  } else if (!username) {
    throw new Error(`TESTER_USERNAME is not defined`);
  } else if (!password) {
    throw new Error(`TESTER_PASSWORD is not defined`);
  }

  const options = {
    chromeFlags: [
      '--headless',
      '--disable-gpu',
      '--disable-dev-shm-usage',
      '--enable-logging',
      '--v=1',
      '--remote-debugging-port=9222'
    ],
    output: 'json'
  };

  const puppeteerModule = await importModule('puppeteer');
  const puppeteer = puppeteerModule.default || puppeteerModule;
  const fetchModule = await importModule('node-fetch');
  const fetch = fetchModule.default;
  const URLModule = await importModule('url');
  const URL = URLModule.default || URLModule;

  console.log(`Testing: ${testURL}`);

  function containsControlCharacters(str) {
    return /[\b\f\n\r\t\v]/.test(str);
  }

  async function isSiteAvailable(url = 'http://localhost', timeout = 10000, interval = 10000) {
    const startTime = Date.now();

    while (Date.now() - startTime < timeout) {
      try {
        console.log(`Checking site availability: ${url}`);
        const response = await fetch(`${url}`);
        if (response.ok) {
          console.log(`Site is available at: ${url}`);
          return true;
        }
      } catch (error) {
        // Ignore errors and continue retrying
        console.log(`Error checking site availability: ${error.message}`);
      }
      await new Promise(resolve => setTimeout(resolve, interval));
    }
    throw new Error(`Site ${url} is not available after ${timeout / 1000} seconds`);
  }

  async function runLighthouse(url, options, config = null) {
    // Wait for the site to be available
    await isSiteAvailable(url);

    // Load chrome-launcher dynamically
    const chromeLauncherModule = await importModule('chrome-launcher');
    const chromeLauncher = chromeLauncherModule.default || chromeLauncherModule;

    let chrome;
    let lighthouseOptions;

    try {
      // Launch Chrome using chrome-launcher
      console.log('Launching Chrome...');
      chrome = await chromeLauncher.launch({
        chromePath: 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe', // Update this path if necessary
        chromeFlags: [
          '--headless',
          '--disable-gpu'
        ]
      });

      console.log(`Chrome launched with PID: ${chrome.pid}`);
      console.log(`Chrome debugging port running on ${chrome.port}`);

      // Use the dynamically assigned port for Lighthouse
      lighthouseOptions = {
        ...options,
        port: chrome.port
      };

    } catch (error) {
      console.error('Error launching Chrome:', error);
    }

    if (!chrome) {
      throw new Error('Chrome did not launch successfully');
    }

    const browser = await puppeteer.launch();
    // const browser = await puppeteer.connect({
    //   browserWSEndpoint: `ws://127.0.0.1:${chrome.port}/devtools/browser/${chrome.process().pid}`
    // });
    const browserWSEndpoint = browser.wsEndpoint();
    console.log(`Browser WebSocket endpoint: ${browserWSEndpoint}`);

    console.log(`Open new page...`);
    const page = await browser.newPage();

    // Detect encoding issues/warnings in returned HTML
    const detectEncodingIssues = ['â', '€', '™', 'Â', 'œ', ''];
    let errors = new Array();
    let warnings = new Array();

    // Import Lighthouse
    const lighthouse = (await import('lighthouse')).default;
    const fs = (await import('fs')).default;
    const fsp = (await import('fs')).promises;

    const http_https = url.indexOf('localhost') === 0 ? 'http' : 'https';
    console.log('Go to URL: ', url);
    await page.goto(
      http_https + '://' + url,
      { waitUntil: 'networkidle0' }
    );

    console.log('Capture screenshot of initial load');
    await page.screenshot({path: fileDir + '00_initial_load.png'});

    console.log('Click on link to open login: a.sysadmin-login');
    await page.click('a.sysadmin-login');

    // Check that the username and password are set and are strings
    if (typeof username !== 'string' || typeof password !== 'string') {
      throw new Error('TESTER_USERNAME (' + username + ') and TESTER_PASSWORD must be set and must be strings');
    }

    // Check if the username field exists
    const usernameField = await page.$('#admin-login input[name="email"]');
    if (!usernameField) {
      throw new Error('No element found for selector: #admin-login input[name="email"]');
    }
    console.log('Login as: ', username);
    await page.type('#admin-login input[name="email"]', username);

    // Check if the password field exists
    const passwordField = await page.$('#admin-login input[name="password"]');
    if (!passwordField) {
      throw new Error('No element found for selector: #admin-login input[name="password"]');
    }
    console.log('Entering password: **** ');
    await page.type('#admin-login input[name="password"]', password);

    console.log('Capture screenshot before login attempt');
    await page.screenshot({path: fileDir + '00_before_login_click.png'});
    const content = await page.content();
    await fsp.writeFile(fileDir + '00_before_login.html', content);

    // Wait for both the click and navigation
    console.log('Click on login button: #admin-login button[type="submit"]');
    await Promise.all([
      page.click('#admin-login button[type="submit"]'),
      page.waitForNavigation({timeout: 60000}),
    ]);

    // Click close button on initial notification
    console.log('Click on close button: #messageHeader button');
    await page.click('#messageHeader button')

    const cookies = await page.cookies();
    // console.log('cookies: ', JSON.stringify(cookies));

    console.log('Capture screenshot after login attempt');
    await page.screenshot({path: fileDir + '00_after_login_click.png'});

    console.log('Testing for encoding issues...');
    for (const char of detectEncodingIssues) {
      if (content.includes(char)) {
        console.log('Issue detected: ', char);
        errors.push(`Found improperly encoded character "${char}" in the HTML content of: ${path}`);
        // throw new Error(`Found improperly encoded character "${char}" in the HTML content`);
      }
    }

    // Define the paths you want to navigate
    const paths = [
      '/dashboard',
      '/goal/current',
      '/goal/20011',
      '/conversation/upcoming',
      '/my-team/my-employees'
    ];

    const pathCount = paths.length;
    let pathsPassed = 0;
    let pathsFailed = 0;
    let results = [];

    // Loop over the paths and run Lighthouse on each one
    for (const path of paths) {

      const url = process.env.APP_HOST_URL + path;
      console.log(`Go to URL: ${url}`);

      await page.setCookie(...cookies);
      // const {lhr} = await lighthouse(url, options, config);
      await page.goto(url, { waitUntil: 'networkidle0' }); // Navigate to the new URL

      // Run Lighthouse
      try {
        console.log(`Running Lighthouse for URL: ${url}`);
        // const { lhr } = await lighthouse(url, options, config);
        const { lhr } = await lighthouse(url, lighthouseOptions, config);
        console.log(`Lighthouse run completed for URL: ${url}`);

        // Get the scores
        const accessibilityScore = lhr.categories.accessibility.score * 100;
        const performanceScore = lhr.categories.performance.score * 100;
        const bestPracticesScore = lhr.categories['best-practices'].score * 100;
        const fileNum = pathsPassed + 1;
        const filename = fileDir + fileNum.toString() + '_' + path.replace(/\W+/g, "_");

        console.log(`Accessibility score: ${accessibilityScore}`);
        console.log(`Performance score: ${performanceScore}`);
        console.log(`Best Practices score: ${bestPracticesScore}`);

        // Check for circular dependencies
        const networkRequests = lhr.audits['network-requests'].details.items;
        const hasCycle = networkRequests.some(item => item.cycle);
        if (hasCycle) {
          console.warn('!! Invalid dependency graph created, cycle detected');
        } else {
          console.log('No circular dependencies detected');
        }

        // Log detailed information about network requests
        // console.log('Network requests:', networkRequests);

        const pageContent = await page.content();
        await fsp.writeFile(filename + '.html', content);
        await page.screenshot({path: filename + '.png'});

        // Verify the scores
        if (accessibilityScore < 90) {
          errors.push(`❌ Accessibility score ${accessibilityScore} is less than 90 for ${path}`);
          pathsFailed++;
        }
        if (performanceScore < 40) {
          errors.push(`❌ Performance score ${performanceScore} is less than 40 for ${path}`);
          pathsFailed++;
        }
        if (bestPracticesScore < 80) {
          errors.push(`❌ Best Practices score ${bestPracticesScore} is less than 80 for ${path}`);
          pathsFailed++;
        }

        console.log('Testing for encoding issues...');
        for (const char of detectEncodingIssues) {
          if (pageContent.includes(char)) {
            warnings.push(`⚠️ Character encoding issue detected on: ${path}`);
          }
        }

        // Add the scores to the results array
        results.push({
          path,
          accessibilityScore,
          performanceScore,
          bestPracticesScore
        });

        pathsPassed++;

      } catch (error) {
        console.error(`Error running Lighthouse for URL: ${url}`, error);
        pathsFailed++;
      }
    }

    await browser.close();
    await chrome.kill();

    // Write the results to a JSON file:
    fs.writeFileSync(fileDir + 'test-results.json', JSON.stringify(results));
    // Convert the results to a markdown table
    let markdown = '| Path | Accessibility Score | Performance Score | Best Practices Score |\n|------|---------------------|-------------------|----------------------|\n';
    for (const result of results) {
      markdown += `| ${result.path} | ${result.accessibilityScore} | ${result.performanceScore} | ${result.bestPracticesScore} |\n`;
    }
    // Write the markdown to a file
    fs.writeFileSync(fileDir + 'test-results.md', markdown);

    let warningString = '';
    if (warnings.length > 0) {
      if (warningString == '') {
        warningString += ' - Warnings: ';
      }
      for (const warning of warnings) {
        warningString += ' - ' + warning;
      }
    }

    if (errors.length > 0) {
      let errorString = '';
      for (const error of errors) {
        errorString += ' - ' + error;
      }
      console.log(`❌ **FAILED**: Some scores (${errors.length}) are below the minimum thresholds (${pathsFailed} of ${pathCount} urls failed) - Errors: ${errorString} ${warningString}`);
    } else {
      console.log(`✔️ **PASSED**: All scores are above the minimum thresholds (${pathsPassed} of ${pathCount} urls passed) ${warningString}`);
    }
  }

  async function runTests() {
    try {
      await runLighthouse(testURL, {});
    } catch (error) {
      console.error(`Error running tests: ${error.message}`);
    }
  }

  runTests();
}

// Call the main function
main().catch(error => {
  console.error(`Error in main function: ${error.message}`);
});
