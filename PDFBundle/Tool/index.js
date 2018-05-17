const puppeteer = require('puppeteer');
const argv = require('yargs')
    .usage(`Gather Digital PDF Generator

Usage: $0 --url [url] --path [filename]`)
    .demandOption(['url', 'path'])
    .describe('url', 'URL to print')
    .describe('scale', 'Scale of webpage rendering')
    .describe('path', 'Output filename / path')
    .version(false)
    .alias('u', 'url')
    .alias('p', 'path')
    .alias('h', 'help')
    .alias('s', 'scale')
    .default('scale', 1)
    .argv;

(async () => {
    try {
        const browser = await puppeteer.launch({
            args: ['--no-sandbox', '--disable-setuid-sandbox'],
            headless: true,
        });
        const page = await browser.newPage();

        page.on('console', msg => {
          for (let i = 0; i < msg.args.length; ++i)
            console.log(`${i}: ${msg.args[i]}`);
        });

        await page.goto(argv.url, {'waitUntil': 'networkidle', 'networkIdleTimeout': 3000});
        await page.emulateMedia('screen');
        await page.pdf({
            path: argv.path,
            format: 'A4',
            printBackground: true,
            scale: argv.scale,
        });

        await browser.close();
        console.info('[success]');
    }
    catch (e) {
        console.error(e.message);
        process.exit();
    }
})();
