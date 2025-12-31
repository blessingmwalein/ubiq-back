import * as React from "react"
import { Upload, X, Video as VideoIcon, Loader2 } from "lucide-react"
import { cn } from "@/lib/utils"
import { Button } from "@/components/ui/button"
import { Label } from "@/components/ui/label"
import { Progress } from "@/components/ui/progress"

interface VideoUploadProps {
  value?: string | File
  onChange: (file: File | null) => void
  progress?: number
  onUploadProgress?: (progress: number) => void
  label?: string
  description?: string
  maxSize?: number // in MB
  accept?: string
  className?: string
  error?: string
  disabled?: boolean
}

export function VideoUpload({
  value,
  onChange,
  onUploadProgress,
  progress = 0,
  label,
  description,
  maxSize = 500, // 500MB default for videos
  accept = "video/*",
  className,
  error,
  disabled = false,
}: VideoUploadProps) {
  const [preview, setPreview] = React.useState<string | null>(null)
  const [isDragging, setIsDragging] = React.useState(false)
  const [isProcessing, setIsProcessing] = React.useState(false)
  const [fileInfo, setFileInfo] = React.useState<{
    name: string
    size: string
    duration?: number
  } | null>(null)
  const fileInputRef = React.useRef<HTMLInputElement>(null)
  const videoRef = React.useRef<HTMLVideoElement>(null)

  React.useEffect(() => {
    if (!value) {
      setPreview(null)
      setFileInfo(null)
      return
    }

    if (typeof value === "string") {
      setPreview(value)
    } else if (value instanceof File) {
      const objectUrl = URL.createObjectURL(value)
      setPreview(objectUrl)

      // Get file info
      setFileInfo({
        name: value.name,
        size: formatFileSize(value.size),
      })

      return () => URL.revokeObjectURL(objectUrl)
    }
  }, [value])

  const formatFileSize = (bytes: number): string => {
    if (bytes < 1024) return bytes + " B"
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(2) + " KB"
    if (bytes < 1024 * 1024 * 1024) return (bytes / (1024 * 1024)).toFixed(2) + " MB"
    return (bytes / (1024 * 1024 * 1024)).toFixed(2) + " GB"
  }

  const handleFileSelect = async (file: File) => {
    if (file.size > maxSize * 1024 * 1024) {
      alert(`File size must be less than ${maxSize}MB`)
      return
    }

    if (!file.type.startsWith("video/")) {
      alert("Please select a video file")
      return
    }

    setIsProcessing(true)

    // Simulate processing/validation
    await new Promise((resolve) => setTimeout(resolve, 500))

    onChange(file)
    setIsProcessing(false)

    // Get video duration
    const video = document.createElement("video")
    video.preload = "metadata"
    video.onloadedmetadata = () => {
      setFileInfo((prev) => prev ? { ...prev, duration: video.duration } : null)
      URL.revokeObjectURL(video.src)
    }
    video.src = URL.createObjectURL(file)
  }

  const handleDrop = (e: React.DragEvent) => {
    e.preventDefault()
    setIsDragging(false)

    const file = e.dataTransfer.files[0]
    if (file) {
      handleFileSelect(file)
    }
  }

  const handleDragOver = (e: React.DragEvent) => {
    e.preventDefault()
    setIsDragging(true)
  }

  const handleDragLeave = () => {
    setIsDragging(false)
  }

  const handleFileInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0]
    if (file) {
      handleFileSelect(file)
    }
  }

  const handleRemove = () => {
    onChange(null)
    if (fileInputRef.current) {
      fileInputRef.current.value = ""
    }
  }

  const formatDuration = (seconds: number): string => {
    const hours = Math.floor(seconds / 3600)
    const minutes = Math.floor((seconds % 3600) / 60)
    const secs = Math.floor(seconds % 60)

    if (hours > 0) {
      return `${hours}:${minutes.toString().padStart(2, "0")}:${secs.toString().padStart(2, "0")}`
    }
    return `${minutes}:${secs.toString().padStart(2, "0")}`
  }

  return (
    <div className={cn("space-y-2", className)}>
      {label && (
        <Label>
          {label}
          {description && (
            <span className="text-xs text-muted-foreground ml-2">
              {description}
            </span>
          )}
        </Label>
      )}

      <div
        onDrop={handleDrop}
        onDragOver={handleDragOver}
        onDragLeave={handleDragLeave}
        className={cn(
          "relative border-2 border-dashed rounded-lg transition-colors",
          isDragging && "border-primary bg-primary/5",
          error && "border-destructive",
          disabled && "opacity-50 cursor-not-allowed"
        )}
      >
        {preview ? (
          <div className="relative group">
            <video
              ref={videoRef}
              src={preview}
              controls
              className="w-full h-64 rounded-lg bg-black"
            />

            {fileInfo && (
              <div className="mt-2 p-3 bg-muted rounded-md space-y-1">
                <p className="text-sm font-medium truncate">{fileInfo.name}</p>
                <div className="flex items-center justify-between text-xs text-muted-foreground">
                  <span>Size: {fileInfo.size}</span>
                  {fileInfo.duration && (
                    <span>Duration: {formatDuration(fileInfo.duration)}</span>
                  )}
                </div>
              </div>
            )}

            {!disabled && (
              <Button
                type="button"
                variant="destructive"
                size="sm"
                onClick={handleRemove}
                className="mt-2 w-full"
              >
                <X className="mr-2 h-4 w-4" />
                Remove Video
              </Button>
            )}
          </div>
        ) : (
          <div
            className="flex flex-col items-center justify-center h-64 cursor-pointer"
            onClick={() => !disabled && fileInputRef.current?.click()}
          >
            {isProcessing ? (
              <>
                <Loader2 className="h-12 w-12 text-primary animate-spin mb-4" />
                <p className="text-sm text-muted-foreground">Processing video...</p>
              </>
            ) : (
              <>
                <VideoIcon className="h-12 w-12 text-muted-foreground mb-4" />
                <p className="text-sm text-muted-foreground mb-1">
                  <span className="text-primary font-medium">Click to upload</span>{" "}
                  or drag and drop
                </p>
                <p className="text-xs text-muted-foreground">
                  MP4, WebM, MOV up to {maxSize}MB
                </p>
              </>
            )}
          </div>
        )}

        {progress > 0 && (
          <div className="mt-2 space-y-1">
            <Progress value={progress} />
            <p className="text-xs text-muted-foreground text-center">
              Uploading... {progress}%
            </p>
          </div>
        )}

        <input
          ref={fileInputRef}
          type="file"
          accept={accept}
          onChange={handleFileInputChange}
          className="hidden"
          disabled={disabled}
        />
      </div>

      {error && <p className="text-sm text-destructive">{error}</p>}
    </div>
  )
}
