<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'database.php';

class Post {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getAllPosts() {
        return $this->conn->query("SELECT * FROM posts ORDER BY created_at DESC");
    }

    public function getPost($id) {
        $stmt = $this->conn->prepare("SELECT * FROM posts WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function createPost($title, $content, $author_name) {
        $stmt = $this->conn->prepare("INSERT INTO posts (title, content, author_name, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("sss", $title, $content, $author_name);
        return $stmt->execute();
    }

    public function updatePost($id, $title, $content, $author_name) {
        $stmt = $this->conn->prepare("UPDATE posts SET title = ?, content = ?, author_name = ? WHERE id = ?");
        $stmt->bind_param("sssi", $title, $content, $author_name, $id);
        return $stmt->execute();
    }

    public function deletePost($id) {
        $stmt = $this->conn->prepare("DELETE FROM posts WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}

$postObj = new Post();
$editPost = isset($_GET['edit']) ? $postObj->getPost($_GET['edit']) : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create'])) {
        $postObj->createPost($_POST['title'], $_POST['content'], $_POST['author_name']);
    } elseif (isset($_POST['update'])) {
        $postObj->updatePost($_POST['id'], $_POST['title'], $_POST['content'], $_POST['author_name']);
    } elseif (isset($_POST['delete'])) {
        $postObj->deletePost($_POST['id']);
    }
    header("Location: index.php");
    exit();
}

$posts = $postObj->getAllPosts();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 p-8">

    <div class="max-w-3xl mx-auto bg-white p-8 rounded-xl shadow-lg">
        <h1 class="text-4xl font-bold text-center text-gray-800 mb-6">📝 My Blog</h1>

       <div class="flex items-center justify-between bg-blue-100 text-blue-900 px-6 py-4 rounded-lg shadow-md">
    <p class="text-lg font-semibold flex items-center space-x-2">
        👋 Welcome, <span class="text-blue-700 font-bold"><?= htmlspecialchars($_SESSION['username']) ?></span>!
    </p>
</div><div class="flex justify-end">
    <a href="logout.php" class="mt-4 bg-green-500 text-white px-6 py-2 rounded-md">Logout</a>
</div>


        <!-- Create Post Button -->
        <button onclick="document.getElementById('postForm').classList.toggle('hidden')" 
                class="mt-4 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg w-full font-semibold shadow-md transition-all">
            ➕ Create New Post
        </button>

        <!-- Post Form -->
        <div id="postForm" class="mt-6 bg-gray-100 p-6 rounded-lg shadow-md <?= $editPost ? '' : 'hidden'; ?>">
            <h2 class="text-2xl font-semibold text-gray-700 mb-4">
                <?= $editPost ? "✏️ Edit Post" : "📝 Create New Post"; ?>
            </h2>
            <form method="POST" class="space-y-4">
                <?php if ($editPost): ?>
                    <input type="hidden" name="id" value="<?= $editPost['id'] ?>">
                <?php endif; ?>
                <input type="text" name="title" placeholder="Post Title" class="w-full border border-gray-300 p-3 rounded-lg focus:ring-2 focus:ring-blue-400 focus:outline-none" required value="<?= $editPost['title'] ?? '' ?>">
                <textarea name="content" rows="4" placeholder="Write something amazing..." class="w-full border border-gray-300 p-3 rounded-lg focus:ring-2 focus:ring-blue-400 focus:outline-none" required><?= $editPost['content'] ?? '' ?></textarea>
                <input type="text" name="author_name" placeholder="Author Name" class="w-full border border-gray-300 p-3 rounded-lg focus:ring-2 focus:ring-blue-400 focus:outline-none" required value="<?= $editPost['author_name'] ?? '' ?>">

                <button type="submit" name="<?= $editPost ? "update" : "create"; ?>" 
                        class="w-full <?= $editPost ? "bg-green-600 hover:bg-green-700" : "bg-blue-600 hover:bg-blue-700"; ?> text-white font-semibold px-4 py-2 rounded-lg transition-all">
                    <?= $editPost ? "✅ Update" : "🚀 Publish"; ?>
                </button>
                
                <?php if ($editPost): ?>
                    <a href="index.php" class="text-red-600 block text-center mt-2 font-semibold hover:underline">❌ Cancel</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- All Posts Section -->
        <h2 class="text-2xl font-semibold text-gray-700 mt-8">📚 Recent Posts</h2>
        <div class="space-y-6 mt-4">
            <?php while ($post = $posts->fetch_assoc()): ?>
                <div class="bg-white shadow-lg rounded-lg p-6 border border-gray-200 hover:shadow-xl transition-all">
                    <h3 class="text-xl font-bold text-gray-800"> <?= htmlspecialchars($post['title']) ?> </h3>
                    <p class="text-gray-600 mt-2"> <?= nl2br(htmlspecialchars($post['content'])) ?> </p>
                    <p class="text-sm text-gray-500 mt-2">By <span class="font-semibold"> <?= htmlspecialchars($post['author_name']) ?> </span> | <?= $post['created_at'] ?></p>

                    <!-- Buttons -->
                    <div class="mt-4 flex items-center space-x-4">
                        <a href="index.php?edit=<?= $post['id'] ?>" class="px-4 py-2 bg-blue-500 text-white rounded-lg shadow-md hover:bg-blue-600 transition">
                            ✏️ Edit
                        </a>
                        <form method="POST" class="inline">
                            <input type="hidden" name="id" value="<?= $post['id'] ?>">
                            <button type="submit" name="delete" class="px-4 py-2 bg-red-500 text-white rounded-lg shadow-md hover:bg-red-600 transition">
                                🗑️ Delete
                            </button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

</body>
</html>
