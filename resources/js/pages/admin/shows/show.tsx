import { Head, Link, router, useForm } from "@inertiajs/react"
import AdminLayout from "@/layouts/admin-layout"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Textarea } from "@/components/ui/textarea"
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from "@/components/ui/card"
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from "@/components/ui/dialog"
import {
  Collapsible,
  CollapsibleContent,
  CollapsibleTrigger,
} from "@/components/ui/collapsible"
import { Badge } from "@/components/ui/badge"
import {
  ArrowLeft,
  Plus,
  Edit,
  Trash2,
  ChevronDown,
  Clock,
  Calendar,
  Eye,
  GripVertical,
  Film,
  Play,
} from "lucide-react"
import { useState } from "react"
import { ImageUpload } from "@/components/ui/image-upload"
import { VideoUpload } from "@/components/ui/video-upload"
import { DatePicker } from "@/components/ui/date-picker"
import { useToast } from "@/components/ui/toast"
import { getCsrfHeaders } from "@/lib/csrf"
import { route } from "@/lib/route"
import type { Show, Season, Episode, ContentItem } from "@/types/streaming"

interface Props {
  show: Show & {
    content_item: ContentItem
    seasons: (Season & {
      episodes: (Episode & { content_item: ContentItem })[]
    })[]
  }
}

export default function ShowDetail({ show }: Props) {
  const { success, error: showError } = useToast()
  const [seasonDialogOpen, setSeasonDialogOpen] = useState(false)
  const [episodeDialogOpen, setEpisodeDialogOpen] = useState(false)
  const [editingSeason, setEditingSeason] = useState<Season | null>(null)
  const [editingEpisode, setEditingEpisode] = useState<Episode | null>(null)
  const [selectedSeason, setSelectedSeason] = useState<Season | null>(null)
  const [episodeVideoFile, setEpisodeVideoFile] = useState<File | null>(null)
  const [isUploadingVideo, setIsUploadingVideo] = useState(false)
  const [videoPlayerOpen, setVideoPlayerOpen] = useState(false)
  const [selectedEpisodeVideos, setSelectedEpisodeVideos] = useState<Episode | null>(null)

  const seasonForm = useForm({
    season_number: editingSeason?.season_number || show.seasons.length + 1,
    title: editingSeason?.title || "",
    description: editingSeason?.description || "",
    poster_url: editingSeason?.poster_url || null,
    released_at: editingSeason?.released_at || "",
  })

  const episodeForm = useForm({
    episode_number: 1,
    title: "",
    description: "",
    duration_seconds: 0,
    thumbnail_url: null as string | null,
    released_at: "",
  })

  const handleSeasonSubmit = (e: React.FormEvent) => {
    e.preventDefault()

    if (editingSeason) {
      seasonForm.put(
        route("admin.shows.seasons.update", [show.id, editingSeason.id]),
        {
          onSuccess: () => {
            setSeasonDialogOpen(false)
            setEditingSeason(null)
            seasonForm.reset()
            success("Season updated!", "The season has been updated successfully")
          },
          onError: (errors) => {
            const errorMessage = Object.values(errors).flat().join(", ")
            showError("Failed to update season", errorMessage)
          },
        }
      )
    } else {
      seasonForm.post(route("admin.shows.seasons.store", show.id), {
        onSuccess: () => {
          setSeasonDialogOpen(false)
          seasonForm.reset()
          success("Season created!", "The season has been created successfully")
        },
        onError: (errors) => {
          const errorMessage = Object.values(errors).flat().join(", ")
          showError("Failed to create season", errorMessage)
        },
      })
    }
  }

  const handleEpisodeSubmit = async (e: React.FormEvent) => {
    e.preventDefault()

    if (!selectedSeason) {
      showError("No season selected", "Please select a season first")
      return
    }

    setIsUploadingVideo(true)

    try {
      let thumbnailUrl = episodeForm.data.thumbnail_url;

      // Upload thumbnail first if provided and it's a File
      if (episodeForm.data.thumbnail_url && typeof episodeForm.data.thumbnail_url !== 'string') {
        const formData = new FormData()
        formData.append("file", episodeForm.data.thumbnail_url)
        formData.append("folder", "episodes/thumbnails")

        const uploadResponse = await fetch("/admin/upload/image", {
          method: "POST",
          body: formData,
          headers: getCsrfHeaders(),
        })

        const uploadData = await uploadResponse.json()

        if (uploadResponse.ok && uploadData.success) {
          thumbnailUrl = uploadData.url;
        } else {
          throw new Error(uploadData.message || "Failed to upload thumbnail")
        }
      }

      // Upload video if provided
      if (episodeVideoFile) {
        const formData = new FormData()
        formData.append("file", episodeVideoFile)
        formData.append("folder", "episodes")
        formData.append("content_id", show.content_item_id.toString())

        const uploadResponse = await fetch("/admin/upload/video", {
          method: "POST",
          body: formData,
          headers: getCsrfHeaders(),
        })

        const uploadData = await uploadResponse.json()

        if (!uploadResponse.ok || !uploadData.success) {
          throw new Error(uploadData.message || "Failed to upload video")
        }
      }

      // Prepare form data with uploaded thumbnail URL
      const submissionData = {
        ...episodeForm.data,
        thumbnail_url: thumbnailUrl,
      };

      // Submit episode form
      if (editingEpisode) {
        router.put(
          route("admin.shows.seasons.episodes.update", [
            show.id,
            selectedSeason.id,
            editingEpisode.id,
          ]),
          submissionData,
          {
            onSuccess: () => {
              setEpisodeDialogOpen(false)
              setEditingEpisode(null)
              episodeForm.reset()
              setEpisodeVideoFile(null)
              setIsUploadingVideo(false)
              success("Episode updated!", "The episode has been updated successfully")
            },
            onError: (errors) => {
              const errorMessage = Object.values(errors).flat().join(", ")
              showError("Failed to update episode", errorMessage)
              setIsUploadingVideo(false)
            },
          }
        )
      } else {
        router.post(
          route("admin.shows.seasons.episodes.store", [show.id, selectedSeason.id]),
          submissionData,
          {
            onSuccess: () => {
              setEpisodeDialogOpen(false)
              episodeForm.reset()
              setEpisodeVideoFile(null)
              setIsUploadingVideo(false)
              success("Episode created!", "The episode has been created successfully")
            },
            onError: (errors) => {
              const errorMessage = Object.values(errors).flat().join(", ")
              showError("Failed to create episode", errorMessage)
              setIsUploadingVideo(false)
            },
          }
        )
      }
    } catch (error) {
      console.error("Episode creation error:", error)
      showError("Upload failed", error instanceof Error ? error.message : "Failed to upload")
      setIsUploadingVideo(false)
    }
  }

  const handleDeleteSeason = (season: Season) => {
    if (
      confirm(
        `Are you sure you want to delete Season ${season.season_number}? This will delete all episodes in this season.`
      )
    ) {
      router.delete(route("admin.shows.seasons.destroy", [show.id, season.id]), {
        onSuccess: () => success("Season deleted!", "The season has been deleted successfully"),
        onError: () => showError("Failed to delete", "Could not delete the season"),
      })
    }
  }

  const handleDeleteEpisode = (season: Season, episode: Episode) => {
    if (confirm(`Are you sure you want to delete "${episode.title}"?`)) {
      router.delete(
        route("admin.shows.seasons.episodes.destroy", [
          show.id,
          season.id,
          episode.id,
        ])
      )
    }
  }

  const formatDuration = (seconds: number): string => {
    const hours = Math.floor(seconds / 3600)
    const minutes = Math.floor((seconds % 3600) / 60)
    if (hours > 0) {
      return `${hours}h ${minutes}m`
    }
    return `${minutes}m`
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
      <Head title={`${show.content_item.title} - Shows`} />

      <div className="space-y-6">
        {/* Header */}
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-4">
            <Button variant="ghost" size="sm" asChild>
              <Link href={route("admin.shows.index")}>
                <ArrowLeft className="mr-2 h-4 w-4" />
                Back
              </Link>
            </Button>
            <div>
              <h1 className="text-3xl font-bold">{show.content_item.title}</h1>
              <p className="text-muted-foreground mt-1">
                {show.total_seasons} seasons · {show.total_episodes} episodes
              </p>
            </div>
          </div>
          <div className="flex gap-2">
            {getStatusBadge(show.status)}
            <Button variant="outline" asChild>
              <Link href={route("admin.shows.edit", show.id)}>
                <Edit className="mr-2 h-4 w-4" />
                Edit Show
              </Link>
            </Button>
          </div>
        </div>

        {/* Show Info */}
        <Card>
          <CardHeader>
            <CardTitle>Show Information</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-2 gap-4">
              <div>
                <p className="text-sm text-muted-foreground">Description</p>
                <p className="mt-1">
                  {show.content_item.description || "No description"}
                </p>
              </div>
              <div>
                <p className="text-sm text-muted-foreground">Category</p>
                <p className="mt-1">
                  {show.content_item.category?.title || "N/A"}
                </p>
              </div>
              <div>
                <p className="text-sm text-muted-foreground">Provider</p>
                <p className="mt-1">
                  {show.content_item.provider?.display_name || "N/A"}
                </p>
              </div>
              <div>
                <p className="text-sm text-muted-foreground">Views</p>
                <p className="mt-1">
                  {show.content_item.views_count?.toLocaleString() || 0}
                </p>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Seasons & Episodes */}
        <Card>
          <CardHeader>
            <div className="flex items-center justify-between">
              <div>
                <CardTitle>Seasons & Episodes</CardTitle>
                <CardDescription>
                  Manage seasons and episodes for this show
                </CardDescription>
              </div>
              <Dialog open={seasonDialogOpen} onOpenChange={setSeasonDialogOpen}>
                <DialogTrigger asChild>
                  <Button onClick={() => setEditingSeason(null)}>
                    <Plus className="mr-2 h-4 w-4" />
                    Add Season
                  </Button>
                </DialogTrigger>
                <DialogContent>
                  <form onSubmit={handleSeasonSubmit}>
                    <DialogHeader>
                      <DialogTitle>
                        {editingSeason ? "Edit Season" : "Add New Season"}
                      </DialogTitle>
                      <DialogDescription>
                        Create a new season for this show
                      </DialogDescription>
                    </DialogHeader>

                    <div className="space-y-4 my-4">
                      <div>
                        <Label htmlFor="season_number">Season Number</Label>
                        <Input
                          id="season_number"
                          type="number"
                          value={seasonForm.data.season_number}
                          onChange={(e) =>
                            seasonForm.setData(
                              "season_number",
                              parseInt(e.target.value)
                            )
                          }
                          min={1}
                          required
                        />
                      </div>

                      <div>
                        <Label htmlFor="title">Title (Optional)</Label>
                        <Input
                          id="title"
                          value={seasonForm.data.title || ""}
                          onChange={(e) => seasonForm.setData("title", e.target.value)}
                          placeholder="e.g., The Beginning"
                        />
                      </div>

                      <div>
                        <Label htmlFor="description">Description</Label>
                        <Textarea
                          id="description"
                          value={seasonForm.data.description || ""}
                          onChange={(e: React.ChangeEvent<HTMLTextAreaElement>) =>
                            seasonForm.setData("description", e.target.value)
                          }
                          rows={3}
                        />
                      </div>

                      <ImageUpload
                        label="Season Poster"
                        value={seasonForm.data.poster_url || undefined}
                        onChange={(file) => seasonForm.setData("poster_url", file || null)}
                      />

                      <div>
                        <Label htmlFor="released_at">Release Date</Label>
                        <DatePicker
                          value={seasonForm.data.released_at || undefined}
                          onChange={(value) => seasonForm.setData("released_at", value)}
                          placeholder="Select release date"
                        />
                      </div>
                    </div>

                    <DialogFooter>
                      <Button
                        type="button"
                        variant="outline"
                        onClick={() => setSeasonDialogOpen(false)}
                      >
                        Cancel
                      </Button>
                      <Button type="submit" disabled={seasonForm.processing}>
                        {editingSeason ? "Update" : "Create"} Season
                      </Button>
                    </DialogFooter>
                  </form>
                </DialogContent>
              </Dialog>
            </div>
          </CardHeader>

          <CardContent className="space-y-4">
            {show.seasons.length === 0 ? (
              <div className="text-center py-8 text-muted-foreground">
                <p>No seasons yet. Add your first season to get started.</p>
              </div>
            ) : (
              show.seasons.map((season) => (
                <Collapsible key={season.id}>
                  <Card>
                    <CollapsibleTrigger className="w-full">
                      <CardHeader className="hover:bg-muted/50 transition-colors">
                        <div className="flex items-center justify-between">
                          <div className="flex items-center gap-3 text-left">
                            <ChevronDown className="h-5 w-5 text-muted-foreground" />
                            <div>
                              <CardTitle className="text-lg">
                                Season {season.season_number}
                                {season.title && ` - ${season.title}`}
                              </CardTitle>
                              <CardDescription>
                                {season.episodes.length} episodes
                              </CardDescription>
                            </div>
                          </div>
                          <div className="flex gap-2" onClick={(e) => e.stopPropagation()}>
                            <Button
                              variant="ghost"
                              size="sm"
                              onClick={() => {
                                setSelectedSeason(season)
                                setEditingEpisode(null)
                                episodeForm.setData(
                                  "episode_number",
                                  season.episodes.length + 1
                                )
                                setEpisodeDialogOpen(true)
                              }}
                            >
                              <Plus className="mr-2 h-4 w-4" />
                              Add Episode
                            </Button>
                            <Button
                              variant="ghost"
                              size="sm"
                              onClick={() => {
                                setEditingSeason(season)
                                seasonForm.setData({
                                  season_number: season.season_number,
                                  title: season.title || "",
                                  description: season.description || "",
                                  poster_url: season.poster_url,
                                  released_at: season.released_at || "",
                                })
                                setSeasonDialogOpen(true)
                              }}
                            >
                              <Edit className="h-4 w-4" />
                            </Button>
                            <Button
                              variant="ghost"
                              size="sm"
                              onClick={() => handleDeleteSeason(season)}
                            >
                              <Trash2 className="h-4 w-4" />
                            </Button>
                          </div>
                        </div>
                      </CardHeader>
                    </CollapsibleTrigger>

                    <CollapsibleContent>
                      <CardContent className="pt-0">
                        {season.episodes.length === 0 ? (
                          <div className="text-center py-4 text-sm text-muted-foreground">
                            No episodes yet
                          </div>
                        ) : (
                          <div className="space-y-2">
                            {season.episodes.map((episode) => (
                              <div
                                key={episode.id}
                                className="flex items-center gap-3 p-3 rounded-md border hover:bg-muted/50 transition-colors"
                              >
                                <GripVertical className="h-4 w-4 text-muted-foreground cursor-move" />
                                
                                <div className="w-20 h-12 rounded bg-muted flex-shrink-0 overflow-hidden">
                                  {episode.thumbnail_url ? (
                                    <img
                                      src={typeof episode.thumbnail_url === 'string' ? episode.thumbnail_url : ''}
                                      alt={episode.title}
                                      className="w-full h-full object-cover"
                                    />
                                  ) : (
                                    <div className="w-full h-full flex items-center justify-center">
                                      <Eye className="h-4 w-4 text-muted-foreground" />
                                    </div>
                                  )}
                                </div>

                                <div className="flex-1 min-w-0">
                                  <p className="font-medium truncate">
                                    {episode.episode_number}. {episode.title}
                                  </p>
                                  <div className="flex items-center gap-2 flex-wrap">
                                    {episode.duration_seconds && (
                                      <p className="text-sm text-muted-foreground flex items-center gap-1">
                                        <Clock className="h-3 w-3" />
                                        {formatDuration(episode.duration_seconds)}
                                      </p>
                                    )}
                                    {episode.content_item?.video_assets && episode.content_item.video_assets.length > 0 && (
                                      <Badge 
                                        variant="secondary" 
                                        className="text-xs cursor-pointer hover:opacity-80"
                                        onClick={() => {
                                          setSelectedEpisodeVideos(episode)
                                          setVideoPlayerOpen(true)
                                        }}
                                      >
                                        <Film className="h-3 w-3 mr-1" />
                                        {episode.content_item.video_assets.length} video{episode.content_item.video_assets.length > 1 ? 's' : ''}
                                      </Badge>
                                    )}
                                  </div>
                                </div>

                                <div className="flex gap-2">
                                  <Button
                                    variant="ghost"
                                    size="sm"
                                    onClick={() => {
                                      setSelectedSeason(season)
                                      setEditingEpisode(episode)
                                      episodeForm.setData({
                                        episode_number: episode.episode_number,
                                        title: episode.title,
                                        description: episode.description || "",
                                        duration_seconds: episode.duration_seconds || 0,
                                        thumbnail_url: typeof episode.thumbnail_url === 'string' ? episode.thumbnail_url : null,
                                        released_at: episode.released_at || "",
                                      })
                                      setEpisodeDialogOpen(true)
                                    }}
                                  >
                                    <Edit className="h-4 w-4" />
                                  </Button>
                                  <Button
                                    variant="ghost"
                                    size="sm"
                                    onClick={() => handleDeleteEpisode(season, episode)}
                                  >
                                    <Trash2 className="h-4 w-4" />
                                  </Button>
                                </div>
                              </div>
                            ))}
                          </div>
                        )}
                      </CardContent>
                    </CollapsibleContent>
                  </Card>
                </Collapsible>
              ))
            )}
          </CardContent>
        </Card>

        {/* Episode Dialog */}
        <Dialog open={episodeDialogOpen} onOpenChange={setEpisodeDialogOpen}>
          <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
            <form onSubmit={handleEpisodeSubmit}>
              <DialogHeader>
                <DialogTitle>
                  {editingEpisode ? "Edit Episode" : "Add New Episode"}
                </DialogTitle>
                <DialogDescription>
                  {selectedSeason &&
                    `Add an episode to Season ${selectedSeason.season_number}`}
                </DialogDescription>
              </DialogHeader>

              <div className="space-y-4 my-4">
                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <Label htmlFor="episode_number">Episode Number</Label>
                    <Input
                      id="episode_number"
                      type="number"
                      value={episodeForm.data.episode_number}
                      onChange={(e) =>
                        episodeForm.setData("episode_number", parseInt(e.target.value))
                      }
                      min={1}
                      required
                    />
                  </div>

                  <div>
                    <Label htmlFor="duration_seconds">Duration (seconds)</Label>
                    <Input
                      id="duration_seconds"
                      type="number"
                      value={episodeForm.data.duration_seconds}
                      onChange={(e) =>
                        episodeForm.setData(
                          "duration_seconds",
                          parseInt(e.target.value)
                        )
                      }
                      min={0}
                    />
                  </div>
                </div>

                <div>
                  <Label htmlFor="episode_title">Title</Label>
                  <Input
                    id="episode_title"
                    value={episodeForm.data.title}
                    onChange={(e) => episodeForm.setData("title", e.target.value)}
                    required
                  />
                </div>

                <div>
                  <Label htmlFor="episode_description">Description</Label>
                  <Textarea
                    id="episode_description"
                    value={episodeForm.data.description}
                    onChange={(e: React.ChangeEvent<HTMLTextAreaElement>) =>
                      episodeForm.setData("description", e.target.value)
                    }
                    rows={3}
                  />
                </div>

                <ImageUpload
                  label="Episode Thumbnail"
                  value={episodeForm.data.thumbnail_url || undefined}
                  onChange={(file) => episodeForm.setData("thumbnail_url", file as string | null)}
                />

                <VideoUpload
                  label="Episode Video"
                  description="MP4, WebM, MOV up to 500MB"
                  value={episodeVideoFile || undefined}
                  onChange={(file) => setEpisodeVideoFile(file)}
                  maxSize={500}
                />

                <div>
                  <Label htmlFor="episode_released_at">Release Date</Label>
                  <DatePicker
                    value={episodeForm.data.released_at || undefined}
                    onChange={(value) => episodeForm.setData("released_at", value)}
                    placeholder="Select release date"
                  />
                </div>
              </div>

              <DialogFooter>
                <Button
                  variant="outline"
                  onClick={() => setEpisodeDialogOpen(false)}
                  disabled={isUploadingVideo}
                >
                  Cancel
                </Button>
                <Button type="submit" disabled={episodeForm.processing || isUploadingVideo}>
                  {isUploadingVideo ? "Uploading video..." : editingEpisode ? "Update" : "Create"} Episode
                </Button>
              </DialogFooter>
            </form>
          </DialogContent>
        </Dialog>

        {/* Video Player Dialog */}
        <Dialog open={videoPlayerOpen} onOpenChange={setVideoPlayerOpen}>
          <DialogContent className="max-w-5xl">
            <DialogHeader>
              <DialogTitle>
                {selectedEpisodeVideos && `Episode ${selectedEpisodeVideos.episode_number}: ${selectedEpisodeVideos.title}`}
              </DialogTitle>
              <DialogDescription>
                Manage episode videos
              </DialogDescription>
            </DialogHeader>
            <div className="space-y-4">
              {selectedEpisodeVideos?.content_item?.video_assets && selectedEpisodeVideos.content_item.video_assets.length > 0 ? (
                selectedEpisodeVideos.content_item.video_assets.map((video) => (
                  <div key={video.id} className="border rounded-lg p-4">
                    <div className="flex items-start justify-between mb-3">
                      <div>
                        <h4 className="font-semibold flex items-center gap-2">
                          {video.resolution || video.rendition_key}
                          <Badge variant={video.status === 'ready' ? 'default' : 'secondary'}>
                            {video.status}
                          </Badge>
                        </h4>
                        <p className="text-sm text-muted-foreground">
                          {video.file_size_mb && `${video.file_size_mb} MB`}
                          {video.bitrate && ` • ${video.bitrate} kbps`}
                        </p>
                      </div>
                      <Button
                        variant="destructive"
                        size="sm"
                        onClick={async () => {
                          if (confirm('Delete this video? This cannot be undone.')) {
                            try {
                              const response = await fetch(`/admin/video-assets/${video.id}`, {
                                method: 'DELETE',
                                headers: getCsrfHeaders(),
                              })
                              if (response.ok) {
                                success('Video deleted', 'The video has been deleted successfully')
                                setVideoPlayerOpen(false)
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
                        <Trash2 className="h-4 w-4 mr-2" />
                        Delete
                      </Button>
                    </div>
                    {video.status === 'ready' && video.hls_manifest_key && (
                      <div className="aspect-video bg-black rounded-lg overflow-hidden">
                        <video
                          key={video.id}
                          controls
                          className="w-full h-full"
                          src={
                            video.hls_manifest_key.startsWith('public/')
                              ? video.hls_manifest_key.replace('public/', '/storage/')
                              : video.hls_manifest_key
                          }
                        >
                          Your browser does not support the video tag.
                        </video>
                      </div>
                    )}
                  </div>
                ))
              ) : (
                <p className="text-center text-muted-foreground py-8">
                  No videos uploaded for this episode
                </p>
              )}
            </div>
          </DialogContent>
        </Dialog>
      </div>
    </AdminLayout>
  )
}
