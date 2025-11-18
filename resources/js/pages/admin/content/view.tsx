import { Head, Link, router, useForm } from "@inertiajs/react"
import AdminLayout from "@/layouts/admin-layout"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card"
import {
  Tabs,
  TabsContent,
  TabsList,
  TabsTrigger,
} from "@/components/ui/tabs"
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog"
import {
  Collapsible,
  CollapsibleContent,
  CollapsibleTrigger,
} from "@/components/ui/collapsible"
import { Badge } from "@/components/ui/badge"
import {
  ArrowLeft,
  Edit,
  Play,
  Upload,
  Image as ImageIcon,
  Film,
  Tv,
  Eye,
  Plus,
  ChevronDown,
  ChevronRight,
  Video,
  Trash2,
} from "lucide-react"
import { ImageUpload } from "@/components/ui/image-upload"
import { VideoUpload } from "@/components/ui/video-upload"
import { route } from "@/lib/route"
import { useState } from "react"
import { useToast } from "@/components/ui/toast"
import { getCsrfHeaders } from "@/lib/csrf"
import type { ContentItem, Category, ContentProvider, Show, Season, Episode } from "@/types/streaming"

interface Props {
  content: ContentItem & {
    category?: Category
    provider?: ContentProvider
    show?: Show
  }
}

export default function ContentView({ content }: Props) {
  const { success, error: showError } = useToast()
  const [uploadProgress, setUploadProgress] = useState(0)
  const [isUploading, setIsUploading] = useState(false)
  const [videoPlayerOpen, setVideoPlayerOpen] = useState(false)
  const [selectedVideoUrl, setSelectedVideoUrl] = useState<string>("")
  
  const videoForm = useForm({
    video_file: null as File | null,
  })

  const imageForm = useForm({
    poster: null as File | null,
    thumbnail: null as File | null,
    backdrop: null as File | null,
  })

  const handleVideoUpload = async (e: React.FormEvent) => {
    e.preventDefault()
    
    if (!videoForm.data.video_file) {
      showError("No file selected", "Please select a video file to upload")
      return
    }

    setIsUploading(true)
    const formData = new FormData()
    formData.append("file", videoForm.data.video_file)
    formData.append("folder", "videos")
    formData.append("content_id", content.id)

    try {
      const response = await fetch("/admin/upload/video", {
        method: "POST",
        body: formData,
        headers: getCsrfHeaders(),
      })

      const data = await response.json()

      if (response.ok && data.success) {
        success("Video uploaded!", "The video has been uploaded successfully")
        videoForm.reset()
        router.reload()
      } else {
        showError("Upload failed", data.message || "Failed to upload video")
      }
    } catch (error) {
      console.error("Upload error:", error)
      showError("Upload error", "An error occurred while uploading the video")
    } finally {
      setIsUploading(false)
      setUploadProgress(0)
    }
  }

  const handleImageUpload = async (type: 'poster' | 'thumbnail' | 'backdrop', file: File | null) => {
    if (!file) return

    const formData = new FormData()
    formData.append("file", file)
    formData.append("folder", "images")
    formData.append("content_id", content.id)
    formData.append("type", type)

    try {
      const response = await fetch("/admin/upload/image", {
        method: "POST",
        body: formData,
        headers: getCsrfHeaders(),
      })

      const data = await response.json()

      if (response.ok && data.success) {
        // Update the content with the new image URL using dedicated endpoint
        const updateResponse = await fetch(`/admin/content/${content.id}/images`, {
          method: "PATCH",
          headers: {
            ...getCsrfHeaders(),
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            [type + '_url']: data.url,
          }),
        })

        const updateData = await updateResponse.json()

        if (updateResponse.ok && updateData.success) {
          success("Image uploaded!", `The ${type} image has been uploaded successfully`)
          router.reload({ only: ['content'] })
        } else {
          showError("Failed to save", updateData.message || "Image uploaded but failed to update content")
        }
      } else {
        showError("Upload failed", data.message || "Failed to upload image")
      }
    } catch (error) {
      console.error("Upload error:", error)
      showError("Upload error", "An error occurred while uploading the image")
    }
  }

  const getTypeBadge = (type: string) => {
    const colors = {
      movie: "bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400",
      show: "bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400",
      skit: "bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400",
      afrimation: "bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400",
      real_estate: "bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400",
    }
    return colors[type as keyof typeof colors] || colors.movie
  }

  const getStatusBadge = (visibility: string) => {
    const variants = {
      public: "bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400",
      draft: "bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400",
      private: "bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-400",
    }
    return variants[visibility as keyof typeof variants] || variants.draft
  }

  return (
    <AdminLayout>
      <Head title={`${content.title} - Content Details`} />

      <div className="space-y-6">
        {/* Header */}
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-4">
            <Button variant="ghost" size="sm" asChild>
              <Link href={route("admin.content.index")}>
                <ArrowLeft className="mr-2 h-4 w-4" />
                Back to Content
              </Link>
            </Button>
            <div>
              <div className="flex items-center gap-3">
                <h1 className="text-3xl font-bold">{content.title}</h1>
                <Badge className={getTypeBadge(content.type)}>
                  {content.type}
                </Badge>
                <Badge className={getStatusBadge(content.visibility)}>
                  {content.visibility}
                </Badge>
              </div>
              <p className="text-muted-foreground mt-1">
                {content.category?.title} · {content.views_count.toLocaleString()} views
              </p>
            </div>
          </div>
          <div className="flex gap-2">
            {content.type === "show" && content.show?.id && (
              <Button variant="outline" asChild>
                <Link href={route("admin.shows.show", content.show.id)}>
                  <Tv className="mr-2 h-4 w-4" />
                  Manage Seasons
                </Link>
              </Button>
            )}
            <Button variant="outline" asChild>
              <Link href={route("admin.content.edit", content.id)}>
                <Edit className="mr-2 h-4 w-4" />
                Edit Details
              </Link>
            </Button>
          </div>
        </div>

        {/* Content Tabs */}
        <Tabs defaultValue="overview" className="space-y-6">
          <TabsList>
            <TabsTrigger value="overview">Overview</TabsTrigger>
            <TabsTrigger value="videos">Videos</TabsTrigger>
            <TabsTrigger value="images">Images</TabsTrigger>
            {content.type === "show" && (
              <TabsTrigger value="episodes">Seasons & Episodes</TabsTrigger>
            )}
          </TabsList>

          {/* Overview Tab */}
          <TabsContent value="overview" className="space-y-6">
            <Card>
              <CardHeader>
                <CardTitle>Content Information</CardTitle>
              </CardHeader>
              <CardContent className="grid grid-cols-2 gap-6">
                <div className="space-y-4">
                  <div>
                    <p className="text-sm text-muted-foreground">Title</p>
                    <p className="text-lg font-semibold">{content.title}</p>
                  </div>
                  <div>
                    <p className="text-sm text-muted-foreground">Description</p>
                    <p className="mt-1">{content.description || "No description"}</p>
                  </div>
                  <div>
                    <p className="text-sm text-muted-foreground">Category</p>
                    <p className="mt-1">{content.category?.title || "N/A"}</p>
                  </div>
                  <div>
                    <p className="text-sm text-muted-foreground">Provider</p>
                    <p className="mt-1">{content.provider?.display_name || "N/A"}</p>
                  </div>
                </div>

                <div className="space-y-4">
                  <div>
                    <p className="text-sm text-muted-foreground">Type</p>
                    <Badge className={getTypeBadge(content.type)}>
                      {content.type}
                    </Badge>
                  </div>
                  <div>
                    <p className="text-sm text-muted-foreground">Status</p>
                    <Badge className={getStatusBadge(content.visibility)}>
                      {content.visibility}
                    </Badge>
                  </div>
                  <div>
                    <p className="text-sm text-muted-foreground">Maturity Rating</p>
                    <p className="mt-1 uppercase">{content.maturity_rating}</p>
                  </div>
                  <div>
                    <p className="text-sm text-muted-foreground">Duration</p>
                    <p className="mt-1">
                      {content.duration_seconds
                        ? `${Math.floor(content.duration_seconds / 60)} minutes`
                        : "N/A"}
                    </p>
                  </div>
                  <div>
                    <p className="text-sm text-muted-foreground">Views</p>
                    <p className="mt-1">{content.views_count.toLocaleString()}</p>
                  </div>
                </div>
              </CardContent>
            </Card>

            {/* Thumbnail Preview */}
            {content.thumbnail_url && (
              <Card>
                <CardHeader>
                  <CardTitle>Current Thumbnail</CardTitle>
                </CardHeader>
                <CardContent>
                  <img
                    src={content.thumbnail_url}
                    alt={content.title}
                    className="w-full max-w-2xl rounded-lg"
                  />
                </CardContent>
              </Card>
            )}
          </TabsContent>

          {/* Videos Tab */}
          <TabsContent value="videos" className="space-y-6">
            <Card>
              <CardHeader>
                <CardTitle>Upload Video</CardTitle>
                <CardDescription>
                  Upload the main video file for this content.
                  {content.type === "show" && (
                    <span className="block mt-1 text-yellow-600 dark:text-yellow-500">
                      Note: For TV shows, upload videos for individual episodes instead.
                    </span>
                  )}
                </CardDescription>
              </CardHeader>
              <CardContent>
                <form onSubmit={handleVideoUpload} className="space-y-4">
                  <VideoUpload
                    label={`Video File (${content.type === "movie" ? "Full Movie" : content.type})`}
                    description="MP4, WebM, MOV up to 500MB"
                    value={videoForm.data.video_file || undefined}
                    onChange={(file) => videoForm.setData("video_file", file)}
                    maxSize={500}
                    disabled={content.type === "show"}
                  />

                  {content.type !== "show" && (
                    <Button type="submit" disabled={videoForm.processing || !videoForm.data.video_file}>
                      <Upload className="mr-2 h-4 w-4" />
                      Upload Video
                    </Button>
                  )}
                </form>
              </CardContent>
            </Card>

            {/* Video Assets List */}
            <Card>
              <CardHeader>
                <CardTitle>Uploaded Videos</CardTitle>
                <CardDescription>
                  Video files associated with this content
                </CardDescription>
              </CardHeader>
              <CardContent>
                {content.video_assets && content.video_assets.length > 0 ? (
                  <div className="space-y-4">
                    {content.video_assets.map((asset) => (
                      <div
                        key={asset.id}
                        className="flex items-start gap-4 p-4 border border-gray-200 dark:border-gray-700 rounded-lg"
                      >
                        <div className="flex-shrink-0">
                          <div className="w-32 h-20 bg-gray-100 dark:bg-gray-800 rounded flex items-center justify-center">
                            <Film className="w-8 h-8 text-gray-400" />
                          </div>
                        </div>
                        <div className="flex-1 min-w-0">
                          <div className="flex items-center gap-2 mb-2">
                            <h4 className="text-sm font-medium text-gray-900 dark:text-white">
                              {asset.rendition_key}
                            </h4>
                            <Badge
                              className={
                                asset.status === 'ready'
                                  ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400'
                                  : asset.status === 'processing'
                                  ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400'
                                  : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400'
                              }
                            >
                              {asset.status}
                            </Badge>
                          </div>
                          <div className="text-xs text-gray-500 dark:text-gray-400 space-y-1">
                            {asset.resolution && (
                              <p>Resolution: {asset.resolution}</p>
                            )}
                            {asset.file_size_mb && (
                              <p>Size: {asset.file_size_mb} MB</p>
                            )}
                            {asset.bitrate && (
                              <p>Bitrate: {asset.bitrate} kbps</p>
                            )}
                          </div>
                          {asset.status === 'ready' && asset.hls_manifest_key && (
                            <video
                              controls
                              className="mt-3 w-full max-w-md rounded"
                              src={asset.hls_manifest_key.replace('public/', '/storage/')}
                            >
                              Your browser does not support the video tag.
                            </video>
                          )}
                        </div>
                      </div>
                    ))}
                  </div>
                ) : (
                  <p className="text-sm text-muted-foreground">
                    No videos uploaded yet. Upload a video above to get started.
                  </p>
                )}
              </CardContent>
            </Card>
          </TabsContent>

          {/* Images Tab */}
          <TabsContent value="images" className="space-y-6">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <Card>
                <CardHeader>
                  <CardTitle>Poster Image</CardTitle>
                  <CardDescription>
                    Vertical poster (2:3 aspect ratio recommended)
                  </CardDescription>
                </CardHeader>
                <CardContent>
                  <ImageUpload
                    value={imageForm.data.poster || content.poster_url}
                    onChange={(file) => {
                      imageForm.setData("poster", file)
                      if (file) handleImageUpload('poster', file)
                    }}
                    maxSize={5}
                  />
                  {content.poster_url && (
                    <p className="mt-2 text-xs text-gray-500">Current: {content.poster_url}</p>
                  )}
                </CardContent>
              </Card>

              <Card>
                <CardHeader>
                  <CardTitle>Thumbnail Image</CardTitle>
                  <CardDescription>
                    Thumbnail for lists (16:9 aspect ratio recommended)
                  </CardDescription>
                </CardHeader>
                <CardContent>
                  <ImageUpload
                    value={imageForm.data.thumbnail || content.thumbnail_url}
                    onChange={(file) => {
                      imageForm.setData("thumbnail", file)
                      if (file) handleImageUpload('thumbnail', file)
                    }}
                    maxSize={5}
                  />
                  {content.thumbnail_url && (
                    <p className="mt-2 text-xs text-gray-500">Current: {content.thumbnail_url}</p>
                  )}
                </CardContent>
              </Card>

              <Card className="md:col-span-2">
                <CardHeader>
                  <CardTitle>Backdrop Image</CardTitle>
                  <CardDescription>
                    Wide background image (16:9 aspect ratio recommended)
                  </CardDescription>
                </CardHeader>
                <CardContent>
                  <ImageUpload
                    value={imageForm.data.backdrop || content.backdrop_url}
                    onChange={(file) => {
                      imageForm.setData("backdrop", file)
                      if (file) handleImageUpload('backdrop', file)
                    }}
                    maxSize={10}
                  />
                  {content.backdrop_url && (
                    <p className="mt-2 text-xs text-gray-500">Current: {content.backdrop_url}</p>
                  )}
                </CardContent>
              </Card>
            </div>
          </TabsContent>

          {/* Episodes Tab (for shows only) */}
          {content.type === "show" && (
            <TabsContent value="episodes" className="space-y-6">
              <Card>
                <CardHeader>
                  <div className="flex items-center justify-between">
                    <div>
                      <CardTitle>Seasons & Episodes</CardTitle>
                      <CardDescription>
                        {content.show?.total_seasons || 0} season(s) and{" "}
                        {content.show?.total_episodes || 0} episode(s)
                      </CardDescription>
                    </div>
                    <Button asChild>
                      <Link href={route("admin.shows.show", content.show?.id || "")}>
                        <Plus className="mr-2 h-4 w-4" />
                        Manage Show
                      </Link>
                    </Button>
                  </div>
                </CardHeader>
                <CardContent className="space-y-4">
                  {content.show?.seasons && content.show.seasons.length > 0 ? (
                    content.show.seasons.map((season: Season) => (
                      <Collapsible key={season.id} className="border rounded-lg">
                        <CollapsibleTrigger className="flex items-center justify-between w-full p-4 hover:bg-muted/50 transition-colors">
                          <div className="flex items-center gap-3">
                            <ChevronRight className="h-5 w-5 transition-transform [[data-state=open]_&]:rotate-90" />
                            <div className="text-left">
                              <h4 className="font-semibold">
                                Season {season.season_number}
                                {season.title && ` - ${season.title}`}
                              </h4>
                              <p className="text-sm text-muted-foreground">
                                {season.episode_count} episode(s)
                              </p>
                            </div>
                          </div>
                          {season.poster_url && (
                            <img
                              src={typeof season.poster_url === 'string' ? season.poster_url : ''}
                              alt={`Season ${season.season_number}`}
                              className="h-16 w-12 object-cover rounded"
                            />
                          )}
                        </CollapsibleTrigger>
                        <CollapsibleContent className="px-4 pb-4">
                          <div className="space-y-3 pt-3 border-t">
                            {season.episodes && season.episodes.length > 0 ? (
                              season.episodes.map((episode: Episode) => (
                                <div
                                  key={episode.id}
                                  className="flex items-start gap-4 p-3 border rounded-lg hover:bg-muted/30 transition-colors"
                                >
                                  {/* Episode Thumbnail */}
                                  <div className="flex-shrink-0">
                                    {episode.thumbnail_url ? (
                                      <img
                                        src={typeof episode.thumbnail_url === 'string' ? episode.thumbnail_url : ''}
                                        alt={episode.title}
                                        className="h-20 w-32 object-cover rounded"
                                      />
                                    ) : (
                                      <div className="h-20 w-32 bg-muted rounded flex items-center justify-center">
                                        <Video className="h-8 w-8 text-muted-foreground" />
                                      </div>
                                    )}
                                  </div>

                                  {/* Episode Details */}
                                  <div className="flex-1 min-w-0">
                                    <div className="flex items-start justify-between gap-2 mb-1">
                                      <h5 className="font-medium">
                                        {episode.episode_number}. {episode.title}
                                      </h5>
                                      <Badge variant="outline" className="flex-shrink-0">
                                        {episode.duration_seconds
                                          ? `${Math.floor(episode.duration_seconds / 60)}min`
                                          : "N/A"}
                                      </Badge>
                                    </div>
                                    {episode.description && (
                                      <p className="text-sm text-muted-foreground line-clamp-2 mb-2">
                                        {episode.description}
                                      </p>
                                    )}

                                    {/* Video Assets */}
                                    {episode.content_item?.video_assets && episode.content_item.video_assets.length > 0 && (
                                      <div className="flex flex-wrap gap-2 mt-2">
                                        {episode.content_item.video_assets.map((video) => (
                                          <div key={video.id} className="flex items-center gap-1">
                                            <Badge
                                              variant={video.status === 'ready' ? 'default' : 'secondary'}
                                              className="text-xs cursor-pointer hover:opacity-80"
                                              onClick={() => {
                                                if (video.status === 'ready' && video.hls_manifest_key) {
                                                  const videoUrl = video.hls_manifest_key.startsWith('public/')
                                                    ? video.hls_manifest_key.replace('public/', '/storage/')
                                                    : video.hls_manifest_key
                                                  setSelectedVideoUrl(videoUrl)
                                                  setVideoPlayerOpen(true)
                                                }
                                              }}
                                            >
                                              <Film className="h-3 w-3 mr-1" />
                                              {video.resolution || video.rendition_key}
                                              {video.status === 'processing' && ' (Processing)'}
                                              {video.status === 'failed' && ' (Failed)'}
                                            </Badge>
                                            <Button
                                              variant="ghost"
                                              size="sm"
                                              className="h-5 w-5 p-0"
                                              onClick={async () => {
                                                if (confirm('Delete this video? This cannot be undone.')) {
                                                  try {
                                                    const response = await fetch(`/admin/video-assets/${video.id}`, {
                                                      method: 'DELETE',
                                                      headers: getCsrfHeaders(),
                                                    })
                                                    if (response.ok) {
                                                      success('Video deleted', 'The video has been deleted successfully')
                                                      window.location.reload()
                                                    } else {
                                                      showError('Delete failed', 'Failed to delete video')
                                                    }
                                                  } catch (error) {
                                                    showError('Delete failed', 'An error occurred')
                                                  }
                                                }
                                              }}
                                            >
                                              <Trash2 className="h-3 w-3 text-destructive" />
                                            </Button>
                                          </div>
                                        ))}
                                      </div>
                                    )}
                                    {(!episode.content_item?.video_assets || episode.content_item.video_assets.length === 0) && (
                                      <p className="text-xs text-muted-foreground mt-2">
                                        No videos uploaded
                                      </p>
                                    )}
                                  </div>
                                </div>
                              ))
                            ) : (
                              <p className="text-sm text-muted-foreground text-center py-4">
                                No episodes in this season
                              </p>
                            )}
                          </div>
                        </CollapsibleContent>
                      </Collapsible>
                    ))
                  ) : (
                    <div className="text-center py-8">
                      <Tv className="h-16 w-16 text-muted-foreground mx-auto mb-4" />
                      <h3 className="text-lg font-semibold mb-2">
                        No Seasons Yet
                      </h3>
                      <p className="text-muted-foreground mb-6">
                        Create seasons and episodes for this show
                      </p>
                      <Button asChild>
                        <Link href={route("admin.shows.show", content.show?.id || "")}>
                          <Plus className="mr-2 h-4 w-4" />
                          Add Seasons
                        </Link>
                      </Button>
                    </div>
                  )}
                </CardContent>
              </Card>
            </TabsContent>
          )}
        </Tabs>
      </div>

      {/* Video Player Dialog */}
      <Dialog open={videoPlayerOpen} onOpenChange={setVideoPlayerOpen}>
        <DialogContent className="max-w-4xl">
          <DialogHeader>
            <DialogTitle>Video Preview</DialogTitle>
            <DialogDescription>
              Playing episode video
            </DialogDescription>
          </DialogHeader>
          <div className="aspect-video bg-black rounded-lg overflow-hidden">
            {selectedVideoUrl && (
              <video
                key={selectedVideoUrl}
                controls
                autoPlay
                className="w-full h-full"
                src={selectedVideoUrl}
              >
                Your browser does not support the video tag.
              </video>
            )}
          </div>
        </DialogContent>
      </Dialog>
    </AdminLayout>
  )
}
