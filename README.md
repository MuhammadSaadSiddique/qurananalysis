# QuranAnalysis Project

## Introduction

The QuranAnalysis (QA) Project is a semantic search and intelligence system for the Holy Quran. It provides users, scholars, and researchers with a powerful suite of tools to search the Quran semantically, analyze various aspects of the text, and discover hidden patterns and associations through state-of-the-art visualization techniques.

This project was started as an MSc project at the University of Leeds in 2015, supervised by Eric Atwell. It aims to build upon previous research and provide an open-source framework for Quranic analysis that can facilitate research, boost applications, and foster innovation in this field.

**Website:** [http://www.qurananalysis.com](http://www.qurananalysis.com)

**Thesis:** [QuranAnalysis: A Semantic Search and Intelligence System for the Quran](https://www.researchgate.net/publication/282648776_QuranAnalysis_A_Semantic_Search_and_Intelligence_System_for_the_Quran)

## Features

- **Semantic Search:** Go beyond keyword matching and search the Quran based on conceptual meaning and context.
- **Question Answering:** Ask natural language questions about the Quran.
- **Comprehensive Analysis Tools:** A rich suite of tools for linguistic and statistical analysis, including word frequency, n-grams, POS patterns, collocations, and more.
- **Interactive Visualizations:** Explore the Quran's concepts and their relationships through interactive graphs and word clouds.
- **Rich Data Resources:** Access a wealth of processed data, including a custom Quranic ontology, Uthmani-to-Simple script mappings, and detailed word information.

## Technology Stack

- **Backend:** PHP
- **Frontend:** HTML, CSS, JavaScript, jQuery
- **Data Visualization:** D3.js
- **Database:** SQLite (for user feedback and mailing list)
- **Caching:** APC (Alternative PHP Cache) for performance.

## Setup and Installation

Follow these steps to set up the QuranAnalysis project on a local server.

### Prerequisites

- A web server with PHP support (e.g., Apache, Nginx).
- PHP version 5.4 or higher.
- The `php-sqlite3` extension for SQLite3 support.
- The `php-apc` or `php-apcu` extension for caching. This is crucial for performance as the application loads large data models into memory.

### Installation Steps

1.  **Clone the Repository:**
    ```bash
    git clone https://github.com/karimouda/qurananalysis.git
    cd qurananalysis
    ```

2.  **Set up the Web Server:**
    - Point your web server's document root to the cloned `qurananalysis` directory.
    - Ensure your server has write permissions for the `data/databases/` and `data/cache/` directories, as well as the log files in `data/logs/`.

3.  **Configure API Keys (Optional):**
    - The application uses the Microsoft Translator API for some translation tasks. To enable this, you need to add your own credentials in `libs/microsoft.translator.api.lib.php`.
    - Open the file and replace `"YOUR_ID"` and `"YOUR_SECRET"` with your actual Client ID and Client Secret.

4.  **Load Data Models into Cache:**
    - The application relies on pre-processed data models that are loaded into APC for fast access. The first time you run the application, these models need to be generated and cached.
    - Open your web browser and navigate to the `admin/reload-models.php` script:
      ```
      http://<your-local-domain>/admin/reload-models.php
      ```
    - This script will clear any existing cache and then load all the necessary data from the raw files into APC. This process may take a few minutes. You should see a "DONE" message upon completion.

5.  **Run the Application:**
    - Once the models are loaded, navigate to the project's root URL in your browser:
      ```
      http://<your-local-domain>/
      ```
    - The homepage should now be fully functional.

## Usage

The application is divided into three main sections:

-   **Search:** The main interface for searching the Quran. It supports simple keywords, phrases (in quotes), and natural language questions.
-   **Explore:** An interactive visualization of the Quranic ontology. Click on concepts to explore related topics and verses.
-   **Analyze:** A collection of powerful tools for in-depth linguistic and statistical analysis of the text.

## Project Structure

The repository is organized into the following key directories:

-   `admin/`: Scripts for administrative tasks, such as data generation and ontology extraction.
-   `analysis/`: Contains the UI and backend logic for the various analysis tools.
-   `dal/`: The Data Access Layer, currently containing the SQLite wrapper.
-   `data/`: All raw data files, corpora, ontologies, and cache files.
-   `explore/`: The exploratory search interface.
-   `info/`: Static informational pages (About, FAQ, etc.).
-   `libs/`: Core PHP libraries and third-party JavaScript libraries.
-   `search/`: The backend logic for the main search functionality.
-   `services/`: AJAX endpoints for features like feedback and subscriptions.
-   `test/`: Test scripts for evaluating system accuracy.
-   `tools/`: Utility tools, such as plotting scripts.

## License

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program. If not, see <http://www.gnu.org/licenses/>.

You can use Quran Analysis code, framework, and corpora in your website or application (commercial/non-commercial) provided that you link back to [www.qurananalysis.com](http://www.qurananalysis.com) and sufficient credits are given.