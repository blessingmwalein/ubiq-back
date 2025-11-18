import { Head, Link, useForm } from "@inertiajs/react"
import AdminLayout from "@/layouts/admin-layout"
import { Button } from "@/components/ui/button"
import { Label } from "@/components/ui/label"
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
  CardHeader,
  CardTitle,
} from "@/components/ui/card"
import { ArrowLeft } from "lucide-react"
import { route } from "@/lib/route"
import type { Show, ContentItem } from "@/types/streaming"

interface Props {
  show?: Show & { content_item: ContentItem }
  availableContent?: ContentItem[]
}

export default function ShowForm({ show, availableContent }: Props) {
  const form = useForm({
    content_item_id: show?.content_item_id?.toString() || "",
    status: show?.status || "ongoing",
  })

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault()

    if (show) {
      form.put(route("admin.shows.update", show.id))
    } else {
      form.post(route("admin.shows.store"))
    }
  }

  return (
    <AdminLayout>
      <Head title={show ? "Edit Show" : "Create Show"} />

      <div className="max-w-3xl space-y-6">
        {/* Header */}
        <div className="flex items-center gap-4">
          <Button variant="ghost" size="sm" asChild>
            <Link href={show ? route("admin.shows.show", show.id) : route("admin.shows.index")}>
              <ArrowLeft className="mr-2 h-4 w-4" />
              Back
            </Link>
          </Button>
          <div>
            <h1 className="text-3xl font-bold">
              {show ? "Edit Show" : "Create New Show"}
            </h1>
            <p className="text-muted-foreground mt-1">
              {show
                ? "Update show information"
                : "Create a show from existing content"}
            </p>
          </div>
        </div>

        {/* Form */}
        <form onSubmit={handleSubmit}>
          <Card>
            <CardHeader>
              <CardTitle>Show Details</CardTitle>
              <CardDescription>
                {show
                  ? "Update the show status"
                  : "Select a content item and configure show settings"}
              </CardDescription>
            </CardHeader>

            <CardContent className="space-y-6">
              {!show && availableContent && (
                <div className="space-y-2">
                  <Label htmlFor="content_item_id">
                    Content Item <span className="text-destructive">*</span>
                  </Label>
                  <Select
                    value={form.data.content_item_id}
                    onValueChange={(value) => form.setData("content_item_id", value)}
                  >
                    <SelectTrigger>
                      <SelectValue placeholder="Select a content item..." />
                    </SelectTrigger>
                    <SelectContent>
                      {availableContent.length === 0 ? (
                        <div className="p-2 text-sm text-muted-foreground text-center">
                          No available content items of type "show"
                        </div>
                      ) : (
                        availableContent.map((content) => (
                          <SelectItem key={content.id} value={content.id.toString()}>
                            {content.title}
                          </SelectItem>
                        ))
                      )}
                    </SelectContent>
                  </Select>
                  <p className="text-xs text-muted-foreground">
                    Only content items of type "show" that don't have a show record
                    are listed. Create content first if needed.
                  </p>
                  {form.errors.content_item_id && (
                    <p className="text-sm text-destructive">
                      {form.errors.content_item_id}
                    </p>
                  )}
                </div>
              )}

              {show && (
                <div className="p-4 bg-muted rounded-lg">
                  <p className="text-sm font-medium mb-1">Content Item</p>
                  <p className="text-2xl font-bold">{show.content_item.title}</p>
                  <p className="text-sm text-muted-foreground mt-1">
                    {show.content_item.description}
                  </p>
                </div>
              )}

              <div className="space-y-2">
                <Label htmlFor="status">
                  Status <span className="text-destructive">*</span>
                </Label>
                <Select
                  value={form.data.status}
                  onValueChange={(value) => form.setData("status", value)}
                >
                  <SelectTrigger>
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="ongoing">Ongoing</SelectItem>
                    <SelectItem value="completed">Completed</SelectItem>
                    <SelectItem value="cancelled">Cancelled</SelectItem>
                  </SelectContent>
                </Select>
                <p className="text-xs text-muted-foreground">
                  Current status of the show's production
                </p>
                {form.errors.status && (
                  <p className="text-sm text-destructive">{form.errors.status}</p>
                )}
              </div>

              {show && (
                <div className="grid grid-cols-2 gap-4 pt-4 border-t">
                  <div>
                    <p className="text-sm text-muted-foreground">Total Seasons</p>
                    <p className="text-2xl font-bold">{show.total_seasons}</p>
                  </div>
                  <div>
                    <p className="text-sm text-muted-foreground">Total Episodes</p>
                    <p className="text-2xl font-bold">{show.total_episodes}</p>
                  </div>
                </div>
              )}
            </CardContent>

            <CardContent className="border-t flex justify-between">
              <Button variant="outline" asChild>
                <Link href={show ? route("admin.shows.show", show.id) : route("admin.shows.index")}>
                  Cancel
                </Link>
              </Button>
              <Button type="submit" disabled={form.processing}>
                {show ? "Update Show" : "Create Show"}
              </Button>
            </CardContent>
          </Card>
        </form>

        {!show && availableContent && availableContent.length === 0 && (
          <Card>
            <CardContent className="pt-6">
              <p className="text-center text-muted-foreground mb-4">
                No content items available. Create a content item of type "show" first.
              </p>
              <div className="flex justify-center">
                <Button asChild>
                  <Link href={route("admin.content.create")}>Create Content</Link>
                </Button>
              </div>
            </CardContent>
          </Card>
        )}
      </div>
    </AdminLayout>
  )
}
