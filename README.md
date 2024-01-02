# Cows Abhor Hamburgers

forked from: `https://github.com/redh4re/cows-abhor-hamburgers`

## BRANDAD version

### What is different

The main thing that I've added (besides new InDesign templates): the ability to use a Google Sheet for managing questions and answers – which then can automatically be exported into a UTF-16-encoded `data/$export.csv` file that can easily be imported into an Adobe Indesign template.

**The steps to take:**
1. create a sheet with at least 4 columns; the naming is kinda flexible (they should be individually addressable) but maybe you want to name the columns "white", "black", "white_include", "black_include"
2. fill in your ideas in the "white" and "black" columns
3. the include column is optional, but you can use it to in-/exclude your ideas into the export – whenever the "white_include" or "black_include" column is not empty, the "white" or "black" card on this line will be exported
4. create a [service account](https://support.google.com/a/answer/7378726?hl=en) for your Google sheet and set it up to at least have reading priviledges – download the service account json file into your project folder
5. use `.env-example` to create an `.env` file with your individual sheet and account settings
6. see `#Example` how to call the export script to generate a `.csv` file with your questions and answers
7. open the Adobe Indesign template for either the white or the black cards and use the data import to import the `.csv`file generated in step 6.

### Parameters and options

See `.env-example` for possible options. ~~Most options can be overwritten by command-line parameters.~~

### Example

```bash
php fetch_from_google_sheets.php \
    --csv_filename data/brandad-cards.csv \
    --white_include_name "white_include" \
    --black_include_name "black_include" \
    --white_column_name "white" \
    --black_column_name "black" \
    --env_file "brandad.env"
```

### All command-line parameters

- `--csv-filename`, `-o` (like in "output"; default: __DIR__."/data/cards.csv"): 
- `--range`, `-r` (default: "A:Z"): 
- `--white_column_name`, `-w` (default: "White")

```php
$config  = array(
    "csv_filename" => [
        "alias" => "o",
        "help" => "Output (csv) filename (default: ./data/cards.csv)",
        "default" => __DIR__."/data/cards.csv"
    ],
    "range" => [ 
        "alias" => "r",
        "help" => "Column range in format 'A:Z'",
        "default" => "A:Z"
    ], 
    "white_column_name" => [ 
        "alias" => "w",
        "help" => "Name of the column with your WHITE cards",
        "default" => "White"
    ],
    "white_include_name" => [ 
        "alias" => "y",
        "help" => "(optional) WHITE cards: Name of a column indicating whether to include a row in the result set or not – checks for presence of a string (include) or empty (exclude)"
    ],
    "black_column_name" => [ 
        "alias" => "b",
        "help" => "Name of the column with your BLACK cards",
        "default" => "Black"
    ],
    "black_include_name" => [ 
        "alias" => "z",
        "help" => "(optional) BLACK cards: Name of a column indicating whether to include a row in the result set or not – checks for presence of a string (include) or empty (exclude)"
    ],
    "env_file" => [
        "alias" => "e",
        "default" => __DIR__."/.env",
        "help" => "specify an .env file for Google apps access"
    ]
);
```

## Original README

Cows Abhor Hamburgers is an unofficial expansion for
[Cards Against Humanity](http://cardsagainsthumanity.com). These templates
can be used to create custom cards that are suitable for professional
printing.

The resulting cards are 2.5 x 3.5 inches. The template includes margin and
bleed areas to ensure all text appears on the final card.

## Play digitally

- https://decks.rereadgames.com/ <- build Deck
- https://md.rereadgames.com/ <- play with the provided deck code

## Required font

~~The "Neue Helvetica® Std 75 Bold" font is used to match the Cards Against
Humanity style as closely as possible. The font can be purchased from
linotype: http://www.linotype.com/45470/NeueHelveticaStd75Bold-product.html.~~

The font `Helvetica Neue (Bold)` closely enough resembles the originally used font.

## Creating cards

The card content was originally created in Google Docs as a shared spreadsheet.
Once all cards are written the black and white cards must be exported as
seperate CSV files. Each CSV file must have a "CardText" header row for the
DataMerge to function.

If your cards contain have any special characters (such as © or ®) then you
should confirm the CSV exported correctly.

* Open the appropriate template using Adobe InDesign CC.
* Click Window → Utilities → Data merge
* Click the Menu icon in the upper right of the Data Merge window
* Click "Select Data Source..."
* Navigate to your exported CSV and click Open
* Click the "Create Merged Document" icon in the bottom right of the Data Merge window
  * Ensure "All Records" is selected
  * Ensure "Records per Document Page" is set to "Single Record" 
* Click OK
* A dialog showing "No overset text was generated when merging records." should appear, click OK

The pages list should now have a page per-card you created! At this point you
should verify the layout of each card; it's common to change the size of the
blank space for black cards.

## Export the cards as a PDF

The cards should be delivered to the printing company as a multi-page PDF.
To export the pages from InDesign you should:

* Click File → Export
* Change the "Save as type" to "Adobe PDF (Print)"
* Enter a filename
* Click Save
* Select "[High Quality Print]" from the "Adobe PDF Preset" dropdown
* Go to the Compression section
  * Set Compress to None for Color Images, Grayscale Images, and Monochrome Images
  * Uncheck "Compress Text and Line Art"
* Go to the Marks and Bleeds section
  * Check "Use Document Bleed Settings"
* Click Export
