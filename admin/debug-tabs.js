// Debug script for settings tabs
// Add this to browser console to debug tab visibility

function debugTabs() {
    console.log('=== TAB DEBUG INFO ===');
    
    const tabs = document.querySelectorAll('.settings-tab-content');
    console.log('Total tabs found:', tabs.length);
    
    tabs.forEach((tab, index) => {
        const id = tab.id;
        const classes = tab.classList.toString();
        const computedDisplay = window.getComputedStyle(tab).display;
        const offsetHeight = tab.offsetHeight;
        const hasHidden = tab.classList.contains('hidden');
        const hasShowTab = tab.classList.contains('show-tab');
        const inlineDisplay = tab.style.display;
        
        console.log(`Tab ${index + 1}:`, {
            id: id,
            classes: classes,
            computedDisplay: computedDisplay,
            inlineDisplay: inlineDisplay,
            offsetHeight: offsetHeight,
            hasHidden: hasHidden,
            hasShowTab: hasShowTab,
            visible: offsetHeight > 0,
            parent: tab.parentElement.tagName,
            parentDisplay: window.getComputedStyle(tab.parentElement).display
        });
    });
    
    console.log('=== BUTTON DEBUG ===');
    const buttons = document.querySelectorAll('.tab-button');
    buttons.forEach((btn, index) => {
        console.log(`Button ${index + 1}:`, {
            id: btn.id,
            onclick: btn.getAttribute('onclick'),
            classes: btn.classList.toString(),
            hasActive: btn.classList.contains('active')
        });
    });
}

// Run debug
debugTabs();
