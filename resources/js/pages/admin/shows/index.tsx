import { Head, Link, router } from "@inertiajs/react"
import AdminLayout from "@/layouts/admin-layout"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select"
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from "@/components/ui/card"
import { Badge } from "@/components/ui/badge"
import { Plus, Search, Eye, Edit, Trash2, Film, Tv } from "lucide-react"
import { useState } from "react"
import { route } from "@/lib/route"
import type { Show, ContentItem, PaginatedData } from "@/types/streaming"

interface Props {
  shows: PaginatedData<
    Show & {
      content_item: ContentItem
      seasons_count: number
      episodes_count: number
    }
  >
  filters: {
    search?: string
    status?: string
  }
}

export default function ShowsIndex({ shows, filters }: Props) {
  const [search, setSearch] = useState(filters.search || "")
  const [status, setStatus] = useState(filters.status || "all")

  const handleFilter = () => {
    router.get(
      route("admin.shows.index"),
      { search, status: status === "all" ? "" : status },
      { preserveState: true }
    )
  }

  const handleDelete = (show: Show & { content_item: ContentItem }) => {
    if (
      confirm(
        `Are you sure you want to delete "${show.content_item.title}"? This action cannot be undone.`
      )
    ) {
      router.delete(route("admin.shows.destroy", show.id))
    }
  }

  const getStatusBadge = (status: string) => {
    const variants = {
      ongoing: { variant: "default" as const, label: "Ongoing" },
      completed: { variant: "secondary" as const, label: "Completed" },
      cancelled: { variant: "destructive" as const, label: "Cancelled" },
    }
    const config = variants[status as keyof typeof variants] || variants.ongoing
    return (
      <Badge variant={config.variant} className="capitalize">
        {config.label}
      </Badge>
    )
  }

  return (
    <AdminLayout>
      <Head title="Shows Management" />

      <div className="space-y-6">
        {/* Header */}
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold">Shows Management</h1>
            <p className="text-muted-foreground mt-1">
              Manage TV shows, seasons, and episodes
            </p>
          </div>
          <Button asChild>
            <Link href={route("admin.shows.create")}>
              <Plus className="mr-2 h-4 w-4" />
              Add Show
            </Link>
          </Button>
        </div>

        {/* Filters */}
        <Card>
          <CardContent className="pt-6">
            <div className="flex gap-4">
              <div className="flex-1">
                <div className="relative">
                  <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                  <Input
                    placeholder="Search shows..."
                    value={search}
                    onChange={(e) => setSearch(e.target.value)}
                    onKeyDown={(e) => e.key === "Enter" && handleFilter()}
                    className="pl-9"
                  />
                </div>
              </div>
              <Select value={status} onValueChange={setStatus}>
                <SelectTrigger className="w-48">
                  <SelectValue placeholder="All Status" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">All Status</SelectItem>
                  <SelectItem value="ongoing">Ongoing</SelectItem>
                  <SelectItem value="completed">Completed</SelectItem>
                  <SelectItem value="cancelled">Cancelled</SelectItem>
                </SelectContent>
              </Select>
              <Button onClick={handleFilter}>Filter</Button>
            </div>
          </CardContent>
        </Card>

        {/* Shows Grid */}
        {shows.data.length === 0 ? (
          <Card>
            <CardContent className="flex flex-col items-center justify-center py-12">
              <Tv className="h-12 w-12 text-muted-foreground mb-4" />
              <h3 className="text-lg font-semibold mb-2">No shows found</h3>
              <p className="text-sm text-muted-foreground mb-4">
                Get started by creating your first show
              </p>
              <Button asChild>
                <Link href={route("admin.shows.create")}>
                  <Plus className="mr-2 h-4 w-4" />
                  Add Show
                </Link>
              </Button>
            </CardContent>
          </Card>
        ) : (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {shows.data.map((show: Show & { content_item: ContentItem; seasons_count: number; episodes_count: number }) => (
              <Card key={show.id} className="overflow-hidden">
                <div className="aspect-video relative bg-muted">
                  {show.content_item.thumbnail_url ? (
                    <img
                      src={show.content_item.thumbnail_url}
                      alt={show.content_item.title}
                      className="w-full h-full object-cover"
                    />
                  ) : (
                    <div className="w-full h-full flex items-center justify-center">
                      <Film className="h-12 w-12 text-muted-foreground" />
                    </div>
                  )}
                  <div className="absolute top-2 right-2">
                    {getStatusBadge(show.status)}
                  </div>
                </div>

                <CardHeader>
                  <CardTitle className="line-clamp-1">
                    {show.content_item.title}
                  </CardTitle>
                  <CardDescription className="line-clamp-2">
                    {show.content_item.description || "No description"}
                  </CardDescription>
                </CardHeader>

                <CardContent>
                  <div className="flex items-center justify-between text-sm text-muted-foreground">
                    <div>
                      <span className="font-medium">{show.seasons_count}</span>{" "}
                      {show.seasons_count === 1 ? "Season" : "Seasons"}
                    </div>
                    <div>
                      <span className="font-medium">{show.episodes_count}</span>{" "}
                      {show.episodes_count === 1 ? "Episode" : "Episodes"}
                    </div>
                  </div>
                </CardContent>

                <CardFooter className="flex gap-2 border-t pt-4">
                  <Button asChild variant="outline" size="sm" className="flex-1">
                    <Link href={route("admin.shows.show", show.id)}>
                      <Eye className="mr-2 h-4 w-4" />
                      View
                    </Link>
                  </Button>
                  <Button asChild variant="outline" size="sm" className="flex-1">
                    <Link href={route("admin.shows.edit", show.id)}>
                      <Edit className="mr-2 h-4 w-4" />
                      Edit
                    </Link>
                  </Button>
                  <Button
                    variant="destructive"
                    size="sm"
                    onClick={() => handleDelete(show)}
                  >
                    <Trash2 className="h-4 w-4" />
                  </Button>
                </CardFooter>
              </Card>
            ))}
          </div>
        )}

        {/* Pagination */}
        {shows.data.length > 0 && (
          <div className="flex items-center justify-between">
            <p className="text-sm text-muted-foreground">
              Showing {shows.from} to {shows.to} of {shows.total} shows
            </p>
            <div className="flex gap-2">
              {shows.links.map((link: { url: string | null; label: string; active: boolean }, index: number) => (
                <Button
                  key={index}
                  variant={link.active ? "default" : "outline"}
                  size="sm"
                  disabled={!link.url}
                  onClick={() => link.url && router.get(link.url)}
                  dangerouslySetInnerHTML={{ __html: link.label }}
                />
              ))}
            </div>
          </div>
        )}
      </div>
    </AdminLayout>
  )
}
