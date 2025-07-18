USE [master]
GO
/****** Object:  Database [Cotizacion]    Script Date: 15/05/2025 09:50:14 ******/
CREATE DATABASE [Cotizacion]
 CONTAINMENT = NONE
 ON  PRIMARY 
( NAME = N'Cotizacion', FILENAME = N'C:\Program Files\Microsoft SQL Server\MSSQL16.MSSQLSERVER2022\MSSQL\DATA\Cotizacion.mdf' , SIZE = 8192KB , MAXSIZE = UNLIMITED, FILEGROWTH = 65536KB )
 LOG ON 
( NAME = N'Cotizacion_log', FILENAME = N'C:\Program Files\Microsoft SQL Server\MSSQL16.MSSQLSERVER2022\MSSQL\DATA\Cotizacion_log.ldf' , SIZE = 8192KB , MAXSIZE = 2048GB , FILEGROWTH = 65536KB )
 WITH CATALOG_COLLATION = DATABASE_DEFAULT, LEDGER = OFF
GO
ALTER DATABASE [Cotizacion] SET COMPATIBILITY_LEVEL = 160
GO
IF (1 = FULLTEXTSERVICEPROPERTY('IsFullTextInstalled'))
begin
EXEC [Cotizacion].[dbo].[sp_fulltext_database] @action = 'enable'
end
GO
ALTER DATABASE [Cotizacion] SET ANSI_NULL_DEFAULT OFF 
GO
ALTER DATABASE [Cotizacion] SET ANSI_NULLS OFF 
GO
ALTER DATABASE [Cotizacion] SET ANSI_PADDING OFF 
GO
ALTER DATABASE [Cotizacion] SET ANSI_WARNINGS OFF 
GO
ALTER DATABASE [Cotizacion] SET ARITHABORT OFF 
GO
ALTER DATABASE [Cotizacion] SET AUTO_CLOSE OFF 
GO
ALTER DATABASE [Cotizacion] SET AUTO_SHRINK OFF 
GO
ALTER DATABASE [Cotizacion] SET AUTO_UPDATE_STATISTICS ON 
GO
ALTER DATABASE [Cotizacion] SET CURSOR_CLOSE_ON_COMMIT OFF 
GO
ALTER DATABASE [Cotizacion] SET CURSOR_DEFAULT  GLOBAL 
GO
ALTER DATABASE [Cotizacion] SET CONCAT_NULL_YIELDS_NULL OFF 
GO
ALTER DATABASE [Cotizacion] SET NUMERIC_ROUNDABORT OFF 
GO
ALTER DATABASE [Cotizacion] SET QUOTED_IDENTIFIER OFF 
GO
ALTER DATABASE [Cotizacion] SET RECURSIVE_TRIGGERS OFF 
GO
ALTER DATABASE [Cotizacion] SET  ENABLE_BROKER 
GO
ALTER DATABASE [Cotizacion] SET AUTO_UPDATE_STATISTICS_ASYNC OFF 
GO
ALTER DATABASE [Cotizacion] SET DATE_CORRELATION_OPTIMIZATION OFF 
GO
ALTER DATABASE [Cotizacion] SET TRUSTWORTHY OFF 
GO
ALTER DATABASE [Cotizacion] SET ALLOW_SNAPSHOT_ISOLATION OFF 
GO
ALTER DATABASE [Cotizacion] SET PARAMETERIZATION SIMPLE 
GO
ALTER DATABASE [Cotizacion] SET READ_COMMITTED_SNAPSHOT OFF 
GO
ALTER DATABASE [Cotizacion] SET HONOR_BROKER_PRIORITY OFF 
GO
ALTER DATABASE [Cotizacion] SET RECOVERY FULL 
GO
ALTER DATABASE [Cotizacion] SET  MULTI_USER 
GO
ALTER DATABASE [Cotizacion] SET PAGE_VERIFY CHECKSUM  
GO
ALTER DATABASE [Cotizacion] SET DB_CHAINING OFF 
GO
ALTER DATABASE [Cotizacion] SET FILESTREAM( NON_TRANSACTED_ACCESS = OFF ) 
GO
ALTER DATABASE [Cotizacion] SET TARGET_RECOVERY_TIME = 60 SECONDS 
GO
ALTER DATABASE [Cotizacion] SET DELAYED_DURABILITY = DISABLED 
GO
ALTER DATABASE [Cotizacion] SET ACCELERATED_DATABASE_RECOVERY = OFF  
GO
EXEC sys.sp_db_vardecimal_storage_format N'Cotizacion', N'ON'
GO
ALTER DATABASE [Cotizacion] SET QUERY_STORE = ON
GO
ALTER DATABASE [Cotizacion] SET QUERY_STORE (OPERATION_MODE = READ_WRITE, CLEANUP_POLICY = (STALE_QUERY_THRESHOLD_DAYS = 30), DATA_FLUSH_INTERVAL_SECONDS = 900, INTERVAL_LENGTH_MINUTES = 60, MAX_STORAGE_SIZE_MB = 1000, QUERY_CAPTURE_MODE = AUTO, SIZE_BASED_CLEANUP_MODE = AUTO, MAX_PLANS_PER_QUERY = 200, WAIT_STATS_CAPTURE_MODE = ON)
GO
USE [Cotizacion]
GO
/****** Object:  Table [dbo].[Cliente]    Script Date: 15/05/2025 09:50:14 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Cliente](
	[id_cliente] [int] IDENTITY(1,1) NOT NULL,
	[nombre] [varchar](60) NULL,
	[razon_social] [varchar](60) NULL,
	[ruc] [varchar](30) NULL,
	[celular] [int] NULL,
	[correo] [varchar](50) NULL,
	[fecha_registro] [datetime] NULL,
PRIMARY KEY CLUSTERED 
(
	[id_cliente] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[DetalleCotizacion]    Script Date: 15/05/2025 09:50:14 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[DetalleCotizacion](
	[id_detalle] [int] IDENTITY(1,1) NOT NULL,
	[id_registro] [int] NULL,
	[id_producto] [int] NULL,
	[cantidad] [int] NULL,
	[precio_total] [decimal](10, 2) NULL,
PRIMARY KEY CLUSTERED 
(
	[id_detalle] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[OpcionDetalle]    Script Date: 15/05/2025 09:50:14 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[OpcionDetalle](
	[id_opcion_detalle] [int] IDENTITY(1,1) NOT NULL,
	[id_detalle] [int] NOT NULL,
	[id_opcion] [int] NOT NULL,
PRIMARY KEY CLUSTERED 
(
	[id_opcion_detalle] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[OpcionExtra]    Script Date: 15/05/2025 09:50:14 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[OpcionExtra](
	[id_opcion] [int] IDENTITY(1,1) NOT NULL,
	[descripcion] [varchar](100) NULL,
	[id_producto] [int] NULL,
	[precio_opcion] [decimal](10, 2) NULL,
	[Tipo_cliente] [varchar](20) NULL,
PRIMARY KEY CLUSTERED 
(
	[id_opcion] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Precios]    Script Date: 15/05/2025 09:50:14 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Precios](
	[id_precio] [int] IDENTITY(1,1) NOT NULL,
	[id_producto] [int] NULL,
	[id_opcion] [int] NULL,
	[cantidad_min] [decimal](6, 2) NULL,
	[cantidad_max] [decimal](6, 2) NULL,
	[precio_unitario] [decimal](6, 2) NULL,
	[Tipo_cliente] [varchar](20) NULL,
PRIMARY KEY CLUSTERED 
(
	[id_precio] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Producto]    Script Date: 15/05/2025 09:50:14 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Producto](
	[id_producto] [int] IDENTITY(1,1) NOT NULL,
	[nombre] [varchar](100) NULL,
PRIMARY KEY CLUSTERED 
(
	[id_producto] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[RegistroCotizacion]    Script Date: 15/05/2025 09:50:14 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[RegistroCotizacion](
	[id_registro] [int] IDENTITY(1,1) NOT NULL,
	[id_cliente] [int] NULL,
	[fecha] [datetime] NULL,
PRIMARY KEY CLUSTERED 
(
	[id_registro] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
ALTER TABLE [dbo].[Cliente] ADD  DEFAULT (getdate()) FOR [fecha_registro]
GO
ALTER TABLE [dbo].[RegistroCotizacion] ADD  DEFAULT (getdate()) FOR [fecha]
GO
ALTER TABLE [dbo].[DetalleCotizacion]  WITH CHECK ADD FOREIGN KEY([id_producto])
REFERENCES [dbo].[Producto] ([id_producto])
GO
ALTER TABLE [dbo].[DetalleCotizacion]  WITH CHECK ADD FOREIGN KEY([id_registro])
REFERENCES [dbo].[RegistroCotizacion] ([id_registro])
GO
ALTER TABLE [dbo].[OpcionDetalle]  WITH CHECK ADD FOREIGN KEY([id_detalle])
REFERENCES [dbo].[DetalleCotizacion] ([id_detalle])
GO
ALTER TABLE [dbo].[OpcionDetalle]  WITH CHECK ADD FOREIGN KEY([id_opcion])
REFERENCES [dbo].[OpcionExtra] ([id_opcion])
GO
ALTER TABLE [dbo].[OpcionExtra]  WITH CHECK ADD FOREIGN KEY([id_producto])
REFERENCES [dbo].[Producto] ([id_producto])
GO
ALTER TABLE [dbo].[Precios]  WITH CHECK ADD FOREIGN KEY([id_opcion])
REFERENCES [dbo].[OpcionExtra] ([id_opcion])
GO
ALTER TABLE [dbo].[Precios]  WITH CHECK ADD FOREIGN KEY([id_producto])
REFERENCES [dbo].[Producto] ([id_producto])
GO
ALTER TABLE [dbo].[RegistroCotizacion]  WITH CHECK ADD FOREIGN KEY([id_cliente])
REFERENCES [dbo].[Cliente] ([id_cliente])
GO
USE [master]
GO
ALTER DATABASE [Cotizacion] SET  READ_WRITE 
GO
