# FAU List Filters Block

A comprehensive list filtering component for the FAU Elemental WordPress theme that provides search, filtering, sorting, and view switching capabilities.

## Features

### Search Section
- Full-text search input field with customizable placeholder
- Clear search button that appears when there's text
- Real-time search with debounced input handling

### Filter Section
- Configurable filter dropdown fields
- Dynamic filter options with value/label pairs
- "Show more filters" button to hide additional filters initially
- Filter chips showing active selections
- Individual chip removal with "×" button
- "Clear all" button when multiple filters are active
- Dynamic labels showing selection count

### Sort Section
- Results count display (e.g., "1 to 15 from 284 records")
- View switcher between Cards, Table, and List views
- Sort dropdown with customizable options
- Responsive design adapting to different screen widths

## Block Configuration

### Search Settings
- **Enable Search**: Toggle search functionality on/off
- **Search Placeholder**: Customize the search input placeholder text

### Filter Settings
- **Enable Filters**: Toggle filter functionality on/off
- **Show More Filters Button**: Hide filters after the first 3 and show a toggle button
- **Filter Fields**: Add custom filter fields with options

### View & Sort Settings
- **Enable View Switcher**: Toggle view switching on/off
- **Available Views**: Select which views to offer (Cards, Table, List)
- **Default View**: Set the initial view
- **Enable Sorting**: Toggle sorting functionality on/off
- **Sort Options**: Configure custom sort options with value/label pairs
- **Default Sort**: Set the initial sort option

### Display Settings
- **Show Results Count**: Toggle results count display
- **Results Per Page**: Set pagination size (5-50)
- **Grid Width**: Set container width (8, 10, or 12 columns)

## Usage

1. Add the "FAU List Filters" block to your page or post
2. Configure the block settings in the inspector panel
3. Add filter fields by entering:
   - Filter name (e.g., "Topics")
   - Filter options, one per line in format: `value|label` (e.g., `news|News Articles`)
4. Configure sort options with value/label pairs
5. Set display preferences and grid width

## Frontend Integration

The block emits custom events that other components can listen to:

### Events
- `fauListFiltersChanged`: Triggered when search, filters, or sort changes
- `fauListFiltersViewChanged`: Triggered when view is switched

### Event Data
```javascript
{
  blockId: 'unique-block-id',
  search: 'search term',
  filters: { filterName: { value: 'value', label: 'Label' } },
  sort: 'sort-value',
  view: 'cards|table|list',
  page: 1,
  resultsPerPage: 15
}
```

### JavaScript API
Each block exposes a JavaScript API for programmatic control:

```javascript
const blockElement = document.querySelector('.fau-list-filters');
const api = blockElement.fauListFilters;

// Get current state
const state = api.getCurrentState();

// Set search term
api.setSearch('new search term');

// Set filter value
api.setFilter('Topics', 'news');

// Clear all filters
api.clearAll();

// Update results count
api.updateResults(150);
```

## Styling

The block uses CSS variables and follows FAU branding guidelines:
- Light mode only design
- Responsive grid layout
- Accessible focus states
- High contrast mode support
- Print-friendly styles

## Accessibility Features

- Proper ARIA labels and roles
- Keyboard navigation support
- Screen reader announcements
- High contrast mode support
- Reduced motion preferences respected

## Browser Support

Compatible with all modern browsers that support:
- CSS Grid
- CSS Custom Properties
- ES6 JavaScript features
- Custom Events API 