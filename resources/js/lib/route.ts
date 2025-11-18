// Route helper function to generate URLs
// This is a simplified version - you may want to install ziggy for full Laravel route support

export function route(name: string, params?: any): string {
  const routes: Record<string, string> = {
    // Admin routes
    'admin.dashboard': '/admin',
    'admin.content.index': '/admin/content',
    'admin.content.create': '/admin/content/create',
    'admin.content.show': '/admin/content/:id',
    'admin.content.edit': '/admin/content/:id/edit',
    'admin.categories.index': '/admin/categories',
    'admin.categories.create': '/admin/categories/create',
    'admin.categories.edit': '/admin/categories/:id/edit',
    'admin.providers.index': '/admin/providers',
    'admin.providers.create': '/admin/providers/create',
    'admin.providers.edit': '/admin/providers/:id/edit',
    'admin.shows.index': '/admin/shows',
    'admin.shows.create': '/admin/shows/create',
    'admin.shows.show': '/admin/shows/:show',
    'admin.shows.edit': '/admin/shows/:show/edit',
    'admin.shows.update': '/admin/shows/:show',
    'admin.shows.store': '/admin/shows',
    'admin.shows.destroy': '/admin/shows/:show',
    'admin.shows.seasons.store': '/admin/shows/:show/seasons',
    'admin.shows.seasons.update': '/admin/shows/:show/seasons/:season',
    'admin.shows.seasons.destroy': '/admin/shows/:show/seasons/:season',
    'admin.shows.seasons.episodes.store': '/admin/shows/:show/seasons/:season/episodes',
    'admin.shows.seasons.episodes.update': '/admin/shows/:show/seasons/:season/episodes/:episode',
    'admin.shows.seasons.episodes.destroy': '/admin/shows/:show/seasons/:season/episodes/:episode',
    'admin.shows.seasons.episodes.reorder': '/admin/shows/:show/seasons/:season/episodes/reorder',
    'admin.users.index': '/admin/users',
    'admin.packages.index': '/admin/packages',
    'admin.subscriptions.index': '/admin/subscriptions',
    'admin.analytics': '/admin/analytics',
  }

  let url = routes[name] || name

  // Replace parameters if provided
  if (params) {
    if (Array.isArray(params)) {
      // Replace in order for arrays [show, season, episode]
      const paramNames = url.match(/:[a-z]+/g) || []
      paramNames.forEach((paramName, index) => {
        if (params[index] !== undefined) {
          url = url.replace(paramName, String(params[index]))
        }
      })
    } else if (typeof params === 'object') {
      // Replace by name for objects {show: 1, season: 2}
      Object.entries(params).forEach(([key, value]) => {
        url = url.replace(`:${key}`, String(value))
      })
    } else {
      // Single parameter - replace first occurrence
      url = url.replace(/:[a-z]+/, String(params))
    }
  }

  return url
}
