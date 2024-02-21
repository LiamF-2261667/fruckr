<head>
    <!-- Title -->
    <title>Accessibility - Fruckr</title>

    <!-- Stylesheets -->
    <link rel="stylesheet" href="/css/accessibility.css">
</head>
<body>

<!-- Actual page-related content -->
<main>
    <!-- The page title -->
    <h1>Accessibility</h1>

    <!-- The page description -->
    <p>
        Fruckr is designed to be accessible to all users, including those with disabilities.
    </p>

    <hr>

    <!-- Visual impairment related features -->
    <section>
        <h2>Visual impairment</h2>
        <ul>
            <li>The <strong>colors are</strong> chosen to be <strong>distinguishable</strong> for people with color blindness</li>
            <li>The <strong>font size is large</strong> enough to be readable for user having trouble with reading small text</li>
            <li>The <strong>fonts are easily readable</strong>, except for the big title text, but that text never has any important meaning.</li>
            <li>The <strong>website can be zoomed in</strong> on for any amount, and it will still be functional, also helping those who struggle with reading small text.</li>
            <li>The <strong>contrast between the background and the text is high</strong> enough to be readable for user having trouble with reading low contrast text</li>
            <li>A <strong>backdrop is added to text that sits on top of images</strong>, to furthermore increase the readability.</li>
            <li>All <strong>pages have a specified title</strong>, aiding those who are using screen-readers with understanding what they are viewing.</li>
        </ul>
    </section>

    <hr>

    <!-- motor impairment related features -->
    <section>
        <h2>Motor impairment</h2>
        <ul>
            <li>You can <strong>navigate</strong> and work with the entire website <strong>only using a keyboard</strong>.</li>
            <li>The website makes use of <strong>specific html tags</strong> to aid in programs that run of the semantics of websites.</li>
        </ul>
    </section>

    <hr>

    <!-- Technical issues related features -->
    <section>
        <h2>Technical issues</h2>
        <ul>
            <li>The website is <strong>responsive</strong>, and will <strong>work on any device</strong>, including mobile phones and other devices with any screen-size.</li>
            <li>The website is designed to be <strong>compatible with all modern browsers</strong>.</li>
            <li>
                The website is designed to be <strong>lightweight</strong>, and <strong>will load quickly</strong>, even on slow connections. <br />
                To achieve this, the following techniques have been used:

                <ul class="inner-ul">
                    <li>
                        Images that don't show at the top of the page are loaded after the rest of the webpage.
                        (using loading="lazy")
                    </li>
                    <li>
                        Images that fill a great portion of the screen at the start are indicated to load faster.
                        (using fetchpriority="high")
                    </li>
                    <li>
                        Queries that would take a long time (like calculating the rating of a foodtruck) are denormalized. <br />
                        That way the result can be fetched in an instant instead of having to go through the entire table every time.
                    </li>
                    <li>
                        No navigation elements required to load an image, so these are all directly available when loading a page.
                    </li>
                </ul>
            </li>
        </ul>
    </section>

    <hr>

    <!-- user experience related features -->
    <section>
        <h2>User experience</h2>
        <ul>
            <li>The website is designed to be <strong>intuitive</strong>, and <strong>easy to use</strong>.</li>
            <li>The website is designed to be <strong>visually appealing</strong>.</li>
        </ul>
    </section>

    <hr>

    <p>
        Hopefully these features will help people with all kinds of disabilities to use the website.
    </p>
</main>

</body>
</html>