<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded" rel="stylesheet" />

    <title>{{ $title ?? 'App' }}</title>
</head>

<body>
    <main>
        {{ $slot }}
    </main>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js"
        integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous">
    </script>
    <script>
        // Hide any bottom overlays, bars, or elements that contain "Observation"
        document.addEventListener('DOMContentLoaded', function () {
            const targetText = 'Observation';
            
            function hideObservationElements() {
                // Hide any elements with "Observation" text that are positioned at bottom
                const all = document.querySelectorAll('*');
                all.forEach(el => {
                    try {
                        const txt = (el.innerText || '').trim();
                        if (txt.includes(targetText)) {
                            const style = window.getComputedStyle(el);
                            
                            // Hide fixed positioned elements at bottom
                            if (style.position === 'fixed' || style.position === 'absolute') {
                                if (style.bottom === '0px' || parseInt(style.bottom) <= 50) {
                                    el.style.display = 'none !important';
                                    el.style.visibility = 'hidden !important';
                                    el.style.opacity = '0 !important';
                                }
                            }
                            
                            // Hide elements with cyan/blue background color
                            const bgColor = style.backgroundColor;
                            if (bgColor.includes('rgb(0, 188, 212)') || 
                                bgColor.includes('rgb(23, 162, 184)') ||
                                bgColor.includes('cyan') ||
                                style.backgroundColor.includes('#17a2b8') ||
                                style.backgroundColor.includes('#00bcd4')) {
                                el.style.display = 'none !important';
                            }
                            
                            // Check if parent containers are positioned at bottom
                            let parent = el.parentElement;
                            while (parent) {
                                const parentStyle = window.getComputedStyle(parent);
                                if ((parentStyle.position === 'fixed' || parentStyle.position === 'absolute') &&
                                    (parentStyle.bottom === '0px' || parseInt(parentStyle.bottom) <= 50)) {
                                    parent.style.display = 'none !important';
                                    break;
                                }
                                parent = parent.parentElement;
                            }
                        }
                    } catch (e) {
                        // ignore errors
                    }
                });
                
                // Also hide elements by class/id that might contain observation
                const selectors = [
                    '[class*="observation"]',
                    '[id*="observation"]',
                    '[class*="Observation"]', 
                    '[id*="Observation"]',
                    '.observation-bar',
                    '#observation-overlay'
                ];
                
                selectors.forEach(selector => {
                    document.querySelectorAll(selector).forEach(el => {
                        const style = window.getComputedStyle(el);
                        if (style.position === 'fixed' || style.position === 'absolute') {
                            el.style.display = 'none !important';
                        }
                    });
                });
            }

            // Run immediately
            hideObservationElements();
            
            // Run after a short delay to catch dynamically loaded content
            setTimeout(hideObservationElements, 500);
            setTimeout(hideObservationElements, 1000);
            setTimeout(hideObservationElements, 2000);

            // Observe DOM changes for dynamically injected content
            const observer = new MutationObserver(() => {
                setTimeout(hideObservationElements, 100);
            });
            observer.observe(document.body, { 
                childList: true, 
                subtree: true, 
                attributes: true,
                attributeFilter: ['style', 'class']
            });
        });
    </script>
</body>

</html>