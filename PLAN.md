# BeatSync: CLI to Web Application Transition Plan

## Overview
BeatSync is evolving from a Python CLI tool that compares Spotify playlists with a DJ's local music library into a Laravel web application with Livewire frontend. The web app will enable service-agnostic playlist management (Spotify, Tidal, etc.) with high responsiveness and a scalable architecture.

## 1. Laravel API Development

### Core API Design
- RESTful endpoints following resource-based URLs and proper HTTP methods
- Laravel API Resources for consistent JSON responses
- Standardized error handling with appropriate HTTP status codes
- Authentication via Laravel Sanctum for both web UI and agent communication
- Service-oriented architecture with specialized services for Spotify, Tidal, etc.

### Implementation Strategy
- Create modular controllers and service classes (SpotifyService, TidalService)
- Implement robust validation via Form Requests
- Version API endpoints (e.g., /api/v1/) for future-proofing
- Secure all endpoints with proper authentication and authorization

## 2. Docker & Containerization

### Environment Setup
- Multi-container architecture with Docker Compose
- Separate containers for Laravel app, database, Redis, and queue workers
- Official PHP-FPM Alpine base image with optimized extensions
- Environment-based configuration for dev/prod differences

### Best Practices
- Persistent data storage with Docker volumes
- Non-root container execution for security
- Multi-stage builds to minimize final image size
- Separate queue worker containers for background processing

## 3. Local Agent Development

### Agent Architecture
- Lightweight desktop agent to scan local music files
- Reuse existing Python code with Mutagen for audio metadata extraction
- Secure API communication with Laravel backend using tokens
- Cross-platform compatibility (Windows, macOS, Linux)

### Agent Modes
- On-demand scanning (initial implementation)
- Background service with file system watching (future enhancement)
- Incremental updates to minimize bandwidth and processing

## 4. Component Integration Plan

### Phase 1: Core Implementation (2 weeks)
- Set up Laravel with Jetstream/Livewire/Volt
- Implement Spotify OAuth and playlist fetching
- Create database structure for users, playlists, and tracks

### Phase 2: Local Integration (2 weeks)
- Develop local agent MVP using Python
- Create API endpoints for agent communication
- Implement track comparison and matching logic in Laravel

### Phase 3: Multi-Service Support (3 weeks)
- Add Tidal integration
- Implement cross-service playlist conversion
- Create background job system for long-running operations

### Phase 4: Refinement & Deployment (1 week)
- Optimize performance for large libraries/playlists
- Finalize Docker configuration for production
- Implement monitoring and error tracking
- Package agent for distribution

## 5. Technology Stack

- **Backend**: Laravel 12 with PHP 8.2+
- **Frontend**: Livewire (with Volt) and TailwindCSS
- **Database**: MySQL/PostgreSQL
- **Caching/Queue**: Redis
- **Local Agent**: Python with Mutagen
- **Containerization**: Docker with multi-container setup
- **CI/CD**: GitHub Actions

## 6. Future Enhancements

- Real-time sync with WebSockets
- Additional streaming services (Apple Music, Deezer)
- Analytics dashboard for library statistics
- Mobile companion app
- Collaborative playlist management

## Technical Decisions

The architecture uses a monolithic Laravel application with a thin Python agent rather than a microservice approach. This decision optimizes for:

1. Developer productivity (single codebase maintenance)
2. Simplified deployment and operations
3. Centralized business logic for cross-service operations
4. Easy scaling via horizontal web server expansion
5. Offloading intensive file scanning to client machines